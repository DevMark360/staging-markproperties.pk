<?php

namespace App\Models;

use App\Support\Projects;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

/** A subscriber to new-launch alerts. One row per email address. */
#[Fillable(['email', 'phone', 'city'])]
class LaunchAlert extends Model
{
    protected static function booted(): void
    {
        static::saving(function (LaunchAlert $alert) {
            $alert->email = Str::lower(trim($alert->email));
            $alert->phone_key = self::phoneKey($alert->phone);
        });
    }

    /** "+92 300 1234567", "0092-300-1234567" and "0300 1234567" all become "03001234567". */
    public static function phoneKey(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '0092')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '92') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? '0'.$digits : $digits;
    }

    /**
     * Sign someone up, or update their row if this email is already subscribed. A city is only overwritten when
     * a new one is given. Rows are matched on email only (R2): matching on the phone too let anyone replace
     * another subscriber's email by typing that person's number. The same number with a new email is saved as its
     * own row, so nobody's address is lost (the admin can spot both by the number).
     */
    public static function subscribe(string $email, string $phone, ?string $city): self
    {
        $email = Str::lower(trim($email));

        try {
            $alert = self::firstWhere('email', $email) ?? new self;
            $alert->fill(['email' => $email, 'phone' => $phone, 'city' => $city ?? $alert->city]);

            // A repeat sign-up with nothing new still counts as "latest sign-up"
            $alert->exists && ! $alert->isDirty() ? $alert->touch() : $alert->save();
        } catch (UniqueConstraintViolationException) {
            // The same email sent twice at the same moment: the other request already saved it
            $alert = self::firstWhere('email', $email);
        }

        return $alert;
    }

    public function cityLabel(): ?string
    {
        return $this->city ? Projects::cityName($this->city) : null;
    }
}
