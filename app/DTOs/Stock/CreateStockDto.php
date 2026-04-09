<?php

namespace App\DTOs\Stock;

readonly class CreateStockDto
{
    public function __construct(
        public ?string $modele,
        public ?string $version,
        public ?string $marque,
        public ?string $vin,
        public ?string $color_ex,
        public ?string $color_ex_code,
        public ?string $color_int,
        public ?string $color_int_code,
        public ?bool $reserved,
        public ?int $depot_id,
        public int $lot_id,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            modele: isset($data['modele']) ? (string) $data['modele'] : null,
            version: isset($data['version']) ? (string) $data['version'] : null,
            marque: isset($data['marque']) ? (string) $data['marque'] : null,
            vin: isset($data['vin']) ? (string) $data['vin'] : null,
            color_ex: isset($data['color_ex']) ? (string) $data['color_ex'] : null,
            color_ex_code: isset($data['color_ex_code']) ? (string) $data['color_ex_code'] : null,
            color_int: isset($data['color_int']) ? (string) $data['color_int'] : null,
            color_int_code: isset($data['color_int_code']) ? (string) $data['color_int_code'] : null,
            reserved: array_key_exists('reserved', $data) && $data['reserved'] !== null
                ? (bool) $data['reserved']
                : null,
            depot_id: isset($data['depot_id']) ? (int) $data['depot_id'] : null,
            lot_id: (int) $data['lot_id'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'modele' => $this->modele,
            'version' => $this->version,
            'marque' => $this->marque,
            'vin' => $this->vin,
            'color_ex' => $this->color_ex,
            'color_ex_code' => $this->color_ex_code,
            'color_int' => $this->color_int,
            'color_int_code' => $this->color_int_code,
            'depot_id' => $this->depot_id,
            'lot_id' => $this->lot_id,
        ];

        if ($this->reserved !== null) {
            $data['reserved'] = $this->reserved;
        }

        return array_filter($data, fn ($v) => $v !== null);
    }
}
