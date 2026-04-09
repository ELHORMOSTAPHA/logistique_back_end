<?php

namespace App\DTOs\Stock;

use App\Http\Requests\Stock\UpdateStockRequest;

/** Payload for partial stock updates (validated request body only). */
readonly class UpdateStockDto
{
    /**
     * @param  array<string, mixed>  $attributes
     */
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
        public ?int $lot_id,


    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(UpdateStockRequest $request): self
    {
        return new self(
            modele: $request->validated('modele'),
            version: $request->validated('version'),
            marque: $request->validated('marque'),
            vin: $request->validated('vin'),
            color_ex: $request->validated('color_ex'),
            color_ex_code: $request->validated('color_ex_code'),
            color_int: $request->validated('color_int'),
            color_int_code: $request->validated('color_int_code'),
            reserved: $request->validated('reserved'),
            depot_id: $request->validated('depot_id'),
            lot_id: $request->validated('lot_id'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'modele' => $this->modele,
            'version' => $this->version,
            'marque' => $this->marque,
            'vin' => $this->vin,
            'color_ex' => $this->color_ex,
            'color_ex_code' => $this->color_ex_code,
            'color_int' => $this->color_int,
            'color_int_code' => $this->color_int_code,
        ]);
        if ($this->reserved !== null) {
            $data['reserved'] = $this->reserved;
        }
        if ($this->depot_id !== null) {
            $data['depot_id'] = $this->depot_id;
        }
        if ($this->lot_id !== null) {
            $data['lot_id'] = $this->lot_id;
        }
        return $data;
    }
}
