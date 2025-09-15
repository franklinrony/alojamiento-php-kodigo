<?php

namespace App\Models;

/**
 * Class Reservation
 * Modelo para la tabla reservations
 */
class Reservation extends Model
{
    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $accommodation_id;

    /**
     * @var string
     */
    protected $check_in_date;

    /**
     * @var string
     */
    protected $check_out_date;

    /**
     * @var float
     */
    protected $total_price;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $guests;

    /**
     * @var string|null
     */
    protected $special_requests;

    /**
     * @var User|null
     */
    protected $user;

    /**
     * @var Accommodation|null
     */
    protected $accommodation;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return (int) $this->user_id;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->user_id = $userId;
    }

    /**
     * @return int
     */
    public function getAccommodationId(): int
    {
        return (int) $this->accommodation_id;
    }

    /**
     * @param int $accommodationId
     */
    public function setAccommodationId(int $accommodationId): void
    {
        $this->accommodation_id = $accommodationId;
    }

    /**
     * @return string
     */
    public function getCheckInDate(): string
    {
        return $this->check_in_date;
    }

    /**
     * @param string $checkInDate
     */
    public function setCheckInDate(string $checkInDate): void
    {
        $this->check_in_date = $checkInDate;
    }

    /**
     * @return string
     */
    public function getCheckOutDate(): string
    {
        return $this->check_out_date;
    }

    /**
     * @param string $checkOutDate
     */
    public function setCheckOutDate(string $checkOutDate): void
    {
        $this->check_out_date = $checkOutDate;
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return (float) $this->total_price;
    }

    /**
     * @param float $totalPrice
     */
    public function setTotalPrice(float $totalPrice): void
    {
        $this->total_price = $totalPrice;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getGuests(): int
    {
        return (int) $this->guests;
    }

    /**
     * @param int $guests
     */
    public function setGuests(int $guests): void
    {
        $this->guests = $guests;
    }

    /**
     * @return string|null
     */
    public function getSpecialRequests(): ?string
    {
        return $this->special_requests;
    }

    /**
     * @param string|null $specialRequests
     */
    public function setSpecialRequests(?string $specialRequests): void
    {
        $this->special_requests = $specialRequests;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
        $this->user_id = $user ? $user->getId() : null;
    }

    /**
     * @return Accommodation|null
     */
    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    /**
     * @param Accommodation|null $accommodation
     */
    public function setAccommodation(?Accommodation $accommodation): void
    {
        $this->accommodation = $accommodation;
        $this->accommodation_id = $accommodation ? $accommodation->getId() : null;
    }

    /**
     * Verifica si la reserva está activa
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica si la reserva está cancelada
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verifica si la reserva está completada
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Calcula el número de noches de la reserva
     *
     * @return int
     */
    public function getNights(): int
    {
        $checkIn = new \DateTime($this->check_in_date);
        $checkOut = new \DateTime($this->check_out_date);
        $diff = $checkIn->diff($checkOut);
        return $diff->days;
    }

    /**
     * Verifica si las fechas de la reserva son válidas
     *
     * @return bool
     */
    public function hasValidDates(): bool
    {
        $checkIn = new \DateTime($this->check_in_date);
        $checkOut = new \DateTime($this->check_out_date);
        $today = new \DateTime();

        // Normalizar a fecha (00:00) para evitar falsos negativos por la hora actual
        $checkIn->setTime(0, 0, 0);
        $checkOut->setTime(0, 0, 0);
        $today->setTime(0, 0, 0);

        return $checkIn >= $today && $checkOut > $checkIn;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->created_at ?? null;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    /**
     * Convierte el modelo a un array excluyendo propiedades relacionadas
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'accommodation_id' => $this->accommodation_id,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'guests' => $this->guests,
            'special_requests' => $this->special_requests,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
