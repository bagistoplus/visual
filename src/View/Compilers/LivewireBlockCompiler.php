<?php

namespace BagistoPlus\Visual\View\Compilers;

use Craftile\Core\Data\BlockSchema;
use Craftile\Laravel\Contracts\BlockCompilerInterface;

class LivewireBlockCompiler implements BlockCompilerInterface
{
    public function supports(BlockSchema $blockSchema): bool
    {
        return is_subclass_of($blockSchema->class, \Livewire\Component::class);
    }

    public function compile(BlockSchema $schema, string $hash, string $customAttributesExpr = ''): string
    {
        $contextVar = '$__context'.$hash;
        $blockDataVar = '$__blockData'.$hash;

        return <<<PHP
        <?php
        // Root blocks (no parent) get page context and forward it child blocks via __craftileContext
        if ({$blockDataVar}->parentId === null) {
            {$contextVar} = craftile()->filterContext(get_defined_vars(), {$customAttributesExpr});
        } else {
            {$contextVar} = array_merge(
                isset(\$__craftileContext) ? \$__craftileContext : [],
                {$customAttributesExpr}
            );
        }
        ?>

        @livewire('craftile-{$schema->slug}', [
            'context' => {$contextVar},
            'block' => {$blockDataVar}
        ], key({$blockDataVar}->id))

        <?php
        // Clean up variables to free memory
        unset({$contextVar}, {$blockDataVar});
        ?>
        PHP;
    }
}
