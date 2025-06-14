<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The "booted" method of the model.
     * Method ini akan berjalan otomatis saat user dihapus,
     * untuk menghapus semua data terkait terlebih dahulu.
     */
    protected static function booted()
    {
        static::deleting(function ($user) {
            // Hapus semua data yang berelasi dengan user ini
            // sebelum user itu sendiri dihapus.
            
            // Hapus konsultasi (baik sebagai pasien maupun psikolog)
            $user->consultationsAsUser()->delete();
            $user->consultationsAsPsychologist()->delete();

            // Hapus jadwal ketersediaan jika user adalah psikolog
            if ($user->role === 'psikolog') {
                $user->availabilities()->delete(); // <-- BARIS INI YANG TERLEWAT
            }

            // Hapus data lain yang terkait
            $user->stories()->delete();
            $user->comments()->delete();
            $user->votes()->delete();
            $user->paymentConfirmations()->delete();
            $user->psychologistProfile()->delete();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'psychologist_status',
        'chosen_psychologist_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'membership_expires_at' => 'datetime',
    ];

    //================================================
    // DEFINISI SEMUA RELASI
    //================================================

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function psychologistProfile()
    {
        return $this->hasOne(PsychologistProfile::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'psychologist_id');
    }

    public function consultationsAsUser()
    {
        return $this->hasMany(Consultation::class, 'user_id');
    }

    public function consultationsAsPsychologist()
    {
        return $this->hasMany(Consultation::class, 'psychologist_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function paymentConfirmations()
    {
        return $this->hasMany(PaymentConfirmation::class);
    }

    public function chosenPsychologist()
    {
        return $this->belongsTo(User::class, 'chosen_psychologist_id');
    }

    public function isMember(): bool
    {
        return $this->membership_expires_at && $this->membership_expires_at->isFuture();
    }
}
