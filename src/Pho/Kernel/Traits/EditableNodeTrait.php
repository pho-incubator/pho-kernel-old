<?php

namespace Pho\Kernel\Traits;

use Pho\Kernel\Foundation;

/**
 * // Always load after persistentnode or volatilenodetrait
 */
trait EditableNodeTrait {

    use PersistentNodeTrait {
        PersistentNodeTrait::toArray as persistentNodeToArray;
    }

    /**
     * The list of editors
     *
     * @var VirtualGroup
     */
    protected $editors;

    public function loadEditorsFrame(): bool
    {
        $this->editors = new Foundation\VirtualGroup($this->kernel, $this->creator(), $this->context());
        //if($this->acl()->sticky()) echo "x";
        //$this->acl()->sticky() ? $this->acl()->get("a::") : $this->acl()->get("u::");
        $this->acl()->set("g:".(string) $this->editors->id().":", 
            $this->acl()->sticky() ? $this->acl()->get("a::") : $this->acl()->get("u::")
        );
        //$this->persist();
        return false;
    }

    // not hydrated, can be .
    public function editors(): Foundation\VirtualGroup
    {
        return $this->editors;
    }

    public function toArray(): array
    {
        $array = $this->persistentNodeToArray();
        $array["editors"] = $this->editors->id();
        return $array;
    }

}