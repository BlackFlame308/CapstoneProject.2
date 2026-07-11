<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * RoleQueryBuilder
 *
 * Custom Eloquent query builder for the Role model.
 * Provides transparent query translation so that role names
 * like 'Captain', 'Encoder', 'Household' map to the correct
 * role_key values stored in the database.
 */
class RoleQueryBuilder extends Builder
{
    /** Maps display role names to their possible role_key values */
    private const ROLE_KEY_MAP = [
        'captain'   => ['super_admin', 'admin', 'captain'],
        'encoder'   => ['encoder'],
        'moderator' => ['evac_admin', 'moderator'],
        'personnel' => ['evac_personnel', 'personnel', 'personel', 'rescuer'],
        'personel'  => ['evac_personnel', 'personnel', 'personel', 'rescuer'],
        'household' => ['household_resident', 'household'],
    ];

    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $columns = array_map(
            fn($col) => $col === 'name' ? 'role_name as name' : $col,
            $columns
        );

        return parent::select($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, '=', $val, $boolean);
            }
            return $this;
        }

        if ($column === 'name') {
            $column = 'role_key';
        }

        if (in_array($column, ['role_key', 'role_name'], true)) {
            return $this->applyRoleFilter($column, $operator, $value, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if ($column === 'name') {
            $column = 'role_key';
        }

        if (in_array($column, ['role_key', 'role_name'], true) && is_array($values)) {
            $values = $this->expandRoleKeys($values);
            $column = 'role_key';
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $sql      = preg_replace('/\bname\b/i', 'role_key', $sql);
        $bindings = $this->translateBindings($bindings);
        return parent::whereRaw($sql, $bindings, $boolean);
    }

    public function orderBy($column, $direction = 'asc')
    {
        if ($column === 'name') {
            $column = 'role_name';
        }
        return parent::orderBy($column, $direction);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyRoleFilter(string $column, $operator, $value, string $boolean): static
    {
        $val        = ($value === null && $operator !== null) ? $operator : $value;
        $isNegation = in_array(trim((string) $operator), ['!=', '<>', 'not like'], true);

        if ($column === 'role_key') {
            $operator = Str::slug(strtolower(trim((string) $operator)), '_');
            if ($value !== null) {
                $value = Str::slug(strtolower(trim((string) $value)), '_');
            }
        }

        $lowerVal = strtolower(trim((string) $val));
        $keys     = self::ROLE_KEY_MAP[$lowerVal] ?? null;

        if ($keys) {
            return $isNegation
                ? parent::whereNotIn('role_key', $keys, $boolean)
                : parent::whereIn('role_key', $keys, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    private function expandRoleKeys(array $values): array
    {
        $expanded = [];
        foreach ($values as $val) {
            $lowerVal = strtolower(trim((string) $val));
            $keys     = self::ROLE_KEY_MAP[$lowerVal] ?? [$val];
            array_push($expanded, ...$keys);
        }
        return array_unique($expanded);
    }

    private function translateBindings(array $bindings): array
    {
        $isSqlite = config('database.default') === 'sqlite';

        $map = [
            'captain'   => $isSqlite ? 'captain'   : 'admin',
            'encoder'   => 'encoder',
            'moderator' => $isSqlite ? 'moderator' : 'evac_admin',
            'personnel' => $isSqlite ? 'personel'  : 'evac_personnel',
            'personel'  => $isSqlite ? 'personel'  : 'evac_personnel',
            'household' => $isSqlite ? 'household' : 'household_resident',
        ];

        return array_map(function ($binding) use ($map) {
            if (is_string($binding)) {
                $lowerVal = strtolower(trim($binding));
                return $map[$lowerVal] ?? $binding;
            }
            return $binding;
        }, $bindings);
    }
}
