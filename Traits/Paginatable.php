<?php


namespace Modules\Starter\Traits;

trait Paginatable
{
    protected $perPageMax = 1000;

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        $perPage = request('page_size',10);

        if ($perPage === 'all') {
            $perPage = $this->count();
        }

        return max(1, min($this->perPageMax, (int) $perPage));
    }

    /**
     * @param int $perPageMax
     */
    public function setPerPageMax(int $perPageMax): void
    {
        $this->perPageMax = $perPageMax;
    }
}
