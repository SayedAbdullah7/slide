<?php

namespace App\Helpers;


class Column
{
    public string $data;
    public ?string $name;
    public ?string $title;
    public bool $searchable;
    public bool $orderable;
    public bool $visible;
    public ?string $className;
    public ?string $width;

    public function __construct(
        string $data,
        ?string $name = null,
        ?string $title = null,
        bool $searchable = true,
        bool $orderable = true,
        bool $visible = true,
        ?string $className = null,
        ?string $width = null
    ) {
        $this->data = $data;
        $this->name = $name ?? $data;
        $this->title = $title ?? str_replace('_', ' ', ucfirst(preg_replace('/_id$/', '', $data)));
        $this->searchable = $searchable;
        $this->orderable = $orderable;
        $this->visible = $visible;
        $this->className = $className;
        $this->width = $width;
    }

    /**
     * Static factory method to create a Column instance.
     *
     * @param string $data
     * @return self
     */
    public static function create(string $data): self
    {
        return new self($data);
    }

    /**
     * Fluent method to set the name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Fluent method to set the title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Fluent method to set searchable.
     *
     * @param bool $searchable
     * @return $this
     */
    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * Fluent method to set orderable.
     *
     * @param bool $orderable
     * @return $this
     */
    public function setOrderable(bool $orderable): self
    {
        $this->orderable = $orderable;
        return $this;
    }

    /**
     * Fluent method to set visibility.
     *
     * @param bool $visible
     * @return $this
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Fluent method to set className.
     *
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className): self
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Fluent method to set width.
     *
     * @param string $width
     * @return $this
     */
    public function setWidth(string $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Convert column to array format.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'data' => $this->data,
            'name' => $this->name,
            'title' => $this->title,
            'searchable' => $this->searchable,
            'orderable' => $this->orderable,
            'visible' => $this->visible,
        ];

        if ($this->className !== null) {
            $array['className'] = $this->className;
        }

        if ($this->width !== null) {
            $array['width'] = $this->width;
        }

        return $array;
    }

    /**
     * Implement JsonSerializable to automatically convert to array.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $array = [
            'data' => $this->data,
            'name' => $this->name,
            'title' => $this->title,
            'searchable' => $this->searchable,
            'orderable' => $this->orderable,
            'visible' => $this->visible,
        ];

        if ($this->className !== null) {
            $array['className'] = $this->className;
        }

        if ($this->width !== null) {
            $array['width'] = $this->width;
        }

        return $array;
    }

}
