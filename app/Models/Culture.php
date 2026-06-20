<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Culture
 * 
 * @property int $id
 * @property string $nom_espece
 * @property string $variete
 * @property int $cycle_vegetatif_jours
 * @property float $kc_initial
 * @property float $kc_milieu
 * @property float $kc_final
 * @property float $besoin_hydrique_moyen_mm
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Exploitation[] $exploitations
 *
 * @package App\Models
 */
class Culture extends Model
{
	protected $table = 'cultures';

	protected $casts = [
		'cycle_vegetatif_jours' => 'int',
		'kc_initial' => 'float',
		'kc_milieu' => 'float',
		'kc_final' => 'float',
		'besoin_hydrique_moyen_mm' => 'float'
	];

	protected $fillable = [
		'nom_espece',
		'variete',
		'cycle_vegetatif_jours',
		'kc_initial',
		'kc_milieu',
		'kc_final',
		'besoin_hydrique_moyen_mm'
	];

	public function exploitations()
	{
		return $this->hasMany(Exploitation::class);
	}
}
