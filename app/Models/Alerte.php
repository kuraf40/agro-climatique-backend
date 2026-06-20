<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Alerte
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type_alerte
 * @property string $message
 * @property bool $est_lue
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Alerte extends Model
{
	protected $table = 'alertes';

	protected $casts = [
		'user_id' => 'int',
		'est_lue' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'type_alerte',
		'message',
		'est_lue'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
