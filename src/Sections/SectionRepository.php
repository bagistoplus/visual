<?php

namespace BagistoPlus\Visual\Sections;

class SectionRepository
{
    /**
     * @var \Illuminate\Support\Collection<Section>
     */
    protected $sections;

    public function __construct()
    {
        $this->sections = collect();
    }

    public function all()
    {
        return $this->sections;
    }

    public function add(Section $section)
    {
        $this->sections->put($section->slug, $section);
    }

    public function has(string $slug)
    {
        return $this->sections->has($slug);
    }

    public function get(string $slug)
    {
        return $this->sections->get($slug);
    }
}
