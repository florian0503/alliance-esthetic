<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Règles alignées sur le calendrier JS (lun–ven, créneaux 9h–17h, délai d’un jour).
 */
final class ReservationSlotValidator
{
    public const string TIMEZONE = 'Europe/Paris';

    private const int HOUR_FIRST = 9;

    private const int HOUR_LAST_START = 16;

    /**
     * @return list<string> liste vide si tout est valide
     */
    public function validate(string $dateIso, string $heureLabel): array
    {
        $errors = [];

        if ($dateIso === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateIso)) {
            return ['Date du rendez-vous invalide.'];
        }

        $tz = new \DateTimeZone(self::TIMEZONE);
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateIso, $tz);
        if ($date === false) {
            return ['Date du rendez-vous invalide.'];
        }
        $date = $date->setTime(0, 0, 0);

        $dow = (int) $date->format('N');
        if ($dow >= 6) {
            $errors[] = 'Les réservations ne sont possibles que du lundi au vendredi.';
        }

        $tomorrow = (new \DateTimeImmutable('now', $tz))->modify('+1 day')->setTime(0, 0, 0);
        if ($date < $tomorrow) {
            $errors[] = 'La date choisie est trop proche : réservez au minimum pour le lendemain.';
        }

        $canonical = $this->normalizeHeureLabel($heureLabel);
        if ($canonical === null) {
            $errors[] = 'Créneau horaire non reconnu.';
        }

        return $errors;
    }

    /**
     * Retourne le libellé canonique (ex. "09h – 10h") ou null si invalide.
     */
    public function normalizeHeureLabel(string $raw): ?string
    {
        $raw = trim(preg_replace('/\s+/u', ' ', $raw) ?? '');
        if ($raw === '') {
            return null;
        }

        if (!preg_match('/^(\d{1,2})h\s*[–\-]\s*(\d{1,2})h$/u', $raw, $m)) {
            return null;
        }

        $h1 = (int) $m[1];
        $h2 = (int) $m[2];
        if ($h2 !== $h1 + 1) {
            return null;
        }
        if ($h1 < self::HOUR_FIRST || $h1 > self::HOUR_LAST_START) {
            return null;
        }

        return sprintf('%02dh – %02dh', $h1, $h2);
    }
}
