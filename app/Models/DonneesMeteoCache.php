<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DonneesMeteoCache
 * 
 * @property int $id
 * @property string $commune
 * @property float $temperature_actuelle
 * @property float $pluviometrie_24h
 * @property float $humidite
 * @property float $et0_quotidienne
 * @property Carbon $derniere_mise_a_jour
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class DonneesMeteoCache extends Model
{
	protected $table = 'donnees_meteo_cache';

	protected $casts = [
		'temperature_actuelle' => 'float',
		'pluviometrie_24h' => 'float',
		'humidite' => 'float',
		'et0_quotidienne' => 'float',
		'derniere_mise_a_jour' => 'datetime'
	];

	protected $fillable = [
		'commune',
		'temperature_actuelle',
		'pluviometrie_24h',
		'humidite',
		'et0_quotidienne',
		'derniere_mise_a_jour'
	];
}
