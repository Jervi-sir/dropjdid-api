<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait FormatsModel
{
    public function format(): array
    {
        $data = $this->attributesToArray();

        foreach ($this->formatterRelations() as $relation) {
            if (! $this->relationLoaded($relation)) {
                continue;
            }

            $data[$relation] = $this->formatRelationValue($this->getRelation($relation));
        }

        return $data;
    }

    protected function formatterRelations(): array
    {
        return [];
    }

    protected function formatRelationValue(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            return $value->map(fn (mixed $item): mixed => $this->formatRelationValue($item))->all();
        }

        if ($value instanceof Model) {
            return method_exists($value, 'format') ? $value->format() : $value->toArray();
        }

        return $value;
    }
}
