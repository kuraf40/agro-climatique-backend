<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Exploitation;
use App\Models\DonneesMeteoCache;
use Carbon\Carbon;

class AgroMeteoController extends Controller
{
    /**
     * Générer le tableau de bord agroclimatique de l'agriculteur connecté.
     */
    public function getDashboardData(Request $request)
    {
        // 1. Récupération de l'identifiant de l'agriculteur depuis la session personnalisée
        $userId = $request->session()->get('user_id');

        // 2. Charger l'exploitation de l'agriculteur avec les données de la culture (CaBEV)
        $exploitation = Exploitation::where('user_id', $userId)->first();

        if (!$exploitation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucune exploitation enregistrée pour ce compte.'
            ], 404);
        }

        // Charger la culture associée via l'ORM
        $culture = $exploitation->culture;

        // 3. Récupération des données météo en temps réel (via API ou Cache local)
        $meteo = $this->recupererMeteoLocale($exploitation);

        // 4. ALGORITHME DE DÉCISION AGROCLIMATIQUE (Norme INRAB / Fiche Banikoara)
        // Définition de la fenêtre théorique optimale (1er juillet au 20 juillet pour le maïs 90j)
        $courant = Carbon::now();
        $debutFenetre = Carbon::create($courant->year, 7, 1);
        $finFenetre = Carbon::create($courant->year, 7, 20);

        $statutSemis = "Période non recommandée (Hors calendrier cultural)";
        $uniteDecisionnelle = "Attente du démarrage de la saison de semis";

        // Vérification si la date actuelle est dans la fenêtre de Franquin
        if ($courant->between($debutFenetre, $finFenetre)) {
            // Seuil agronomique de sécurité : il faut au moins 20mm de cumul de pluie
            if ($meteo['pluviometrie_24h'] >= 20.0) {
                $statutSemis = "CONDITIONS EXCELLENTES : Les réserves en eau du sol sont suffisantes. Vous pouvez semer immédiatement.";
                $uniteDecisionnelle = "Feu Vert (Semis optimal)";
            } else {
                $statutSemis = "Fenêtre favorable ouverte, mais humidité insuffisante. Attendez une pluie cumulée >= 20 mm avant de semer.";
                $uniteDecisionnelle = "Feu Orange (Attente de pluie)";
            }
        } elseif ($courant->gt($finFenetre) && is_null($exploitation->date_semis_effective)) {
            $statutSemis = "ALERTE : Fenêtre optimale dépassée. Risque élevé de stress hydrique en fin de cycle (phase critique de floraison).";
            $uniteDecisionnelle = "Feu Rouge (Risque climatique)";
        } elseif (!is_null($exploitation->date_semis_effective)) {
            $statutSemis = "Semis déjà effectué. Suivi de la croissance en cours.";
            $uniteDecisionnelle = "Suivi végétatif";
        }

        // 5. Envoi de la réponse structurée au format JSON pour l'application mobile
        return response()->json([
            'status' => 'success',
            'agriculteur' => $request->session()->get('user_nom'),
            'exploitation' => [
                'nom' => $exploitation->nom_exploitation,
                'commune' => $exploitation->commune,
                'superficie' => $exploitation->superficie_hectares . ' ha'
            ],
            'culture_pratiquee' => [
                'espece' => $culture->nom_espece,
                'variete' => $culture->variete,
                'cycle' => $culture->cycle_vegetatif_jours . ' jours'
            ],
            'meteo_temps_reel' => [
                'temperature' => $meteo['temperature'] . ' °C',
                'humidite' => $meteo['humidite'] . ' %',
                'pluie_24h' => $meteo['pluviometrie_24h'] . ' mm',
                'source' => $meteo['source']
            ],
            'conseil_decisionnel' => [
                'indicateur' => $uniteDecisionnelle,
                'recommandation' => $statutSemis,
                'calendrier_optimal' => 'Du 01 Juillet au 20 Juillet (Zone Soudanienne)'
            ]
        ], 200);
    }

    /**
     * Gestion résiliente de la météo (Collecte API Externe + Système Adaptatif de Cache MySQL)
     */
    private function recupererMeteoLocale($exploitation)
    {
        // Étape A : Vérifier s'il y a des données récentes en cache local (< 30 minutes)
        $cache = DonneesMeteoCache::where('commune', $exploitation->commune)->first();

        if ($cache && Carbon::parse($cache->derniere_mise_a_jour)->diffInMinutes(Carbon::now()) < 30) {
            return [
                'temperature' => $cache->temperature_actuelle,
                'pluviometrie_24h' => $cache->pluviometrie_24h,
                'humidite' => $cache->humidite,
                'source' => 'Cache Local MySQL (Mode Résilience)'
            ];
        }

        // Étape B : Si le cache est absent ou trop vieux, requêter l'API OpenWeatherMap
        try {
            $apiKey = config('services.openweather.key') ?? env('OPENWEATHER_API_KEY');
            
            // Appel HTTP synchrone avec un timeout de 4 secondes pour ne pas bloquer l'application mobile
            $response = Http::timeout(4)->get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $exploitation->latitude,
                'lon' => $exploitation->longitude,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $temp = $data['main']['temp'];
                $hum = $data['main']['humidity'];
                // OpenWeather retourne le volume de pluie des dernières heures si disponible
                $pluie = isset($data['rain']['1h']) ? $data['rain']['1h'] * 24 : (isset($data['rain']['3h']) ? $data['rain']['3h'] * 8 : 0.0);

                // Mettre à jour ou créer l'enregistrement dans notre table de cache
                DonneesMeteoCache::updateOrCreate(
                    ['commune' => $exploitation->commune],
                    [
                        'temperature_actuelle' => $temp,
                        'pluviometrie_24h' => $pluie,
                        'humidite' => $hum,
                        'derniere_mise_a_jour' => Carbon::now()
                    ]
                );

                return [
                    'temperature' => $temp,
                    'pluviometrie_24h' => $pluie,
                    'humidite' => $hum,
                    'source' => 'Flux API OpenWeatherMap'
                ];
            }
        } catch (\Exception $e) {
            // Étape C : Sécurité de repli (FallBack) en cas de coupure internet ou de panne d'API
            if ($cache) {
                return [
                    'temperature' => $cache->temperature_actuelle,
                    'pluviometrie_24h' => $cache->pluviometrie_24h,
                    'humidite' => $cache->humidite,
                    'source' => 'Cache Local (Mode Secours Réseau Dégradé)'
                ];
            }
        }

        // Données par défaut si l'API échoue ET que le cache est totalement vide
        return [
            'temperature' => 29.5,
            'pluviometrie_24h' => 0.0,
            'humidite' => 75,
            'source' => 'Normales Agroclimatiques par défaut (Bénin)'
        ];
    }
}