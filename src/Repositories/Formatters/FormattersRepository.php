<?php namespace Sanatorium\Sync\Repositories\Formatters;


class FormattersRepository implements FormattersRepositoryInterface {

	/**
     * Array of registered namespaces.
     *
     * @var array
     */
    protected $services;

    /**
     * {@inheritDoc}
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * {@inheritDoc}
     */
    public function registerService($key, $service)
    {
        $this->services[$key] = $service;
    }

}
