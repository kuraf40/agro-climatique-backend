<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $nom
 * @property string $prenom
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $telephone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Alerte[] $alertes
 * @property Collection|Exploitation[] $exploitations
 *
 * @package App\Models
 */
class User extends Model
{
	protected $table = 'users';

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'nom',
		'prenom',
		'email',
		'password',
		'role',
		'telephone'
	];

	public function alertes()
	{
		return $this->hasMany(Alerte::class);
	}

	public function exploitations()
	{
		return $this->hasMany(Exploitation::class);
	}
}
