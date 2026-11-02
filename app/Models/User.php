<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'ktp_path',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mengambil seluruh barang milik akun ini.
     */
    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class);
    }

    public function barangFavorit()
    {
        return $this->belongsToMany(
            \App\Models\Barang::class,
            'barang_favorit',
            'user_id',
            'barang_id'
        )->withTimestamps();
    }

    /**
     * Percakapan yang dimulai sebagai pembeli.
     */
    public function percakapanSebagaiPembeli(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * Percakapan sebagai penjual.
     */
    public function percakapanSebagaiPenjual(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    /**
     * Pesan yang dikirim oleh pengguna.
     */
    public function pesanTerkirim(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
