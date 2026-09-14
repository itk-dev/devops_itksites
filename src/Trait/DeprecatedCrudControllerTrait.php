<?php

declare(strict_types=1);

namespace App\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;

/**
 * Flashes a deprecation warning on every page a deprecated CRUD renders.
 *
 * The controller is gone from the admin menu but still routed, so the only way
 * in is a bookmark or an old link. Without the warning the page looks like a
 * part of the admin that is still maintained.
 */
trait DeprecatedCrudControllerTrait
{
    /**
     * The warning shown on index, detail, new and edit.
     */
    abstract protected function getDeprecationNotice(): string;

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        // `delete` and `batchDelete` pass through here too, on their way to a
        // redirect, and set no page name. Flashing there would queue a second
        // copy for the index page that renders next.
        if (null !== $responseParameters->get('pageName')) {
            $this->addFlash('warning', [
                'title' => 'Deprecated',
                // The style prefix is part of the name, as on the menu
                // items: EasyAdmin writes it into `class` verbatim, and
                // FontAwesome draws nothing without it.
                'icon' => 'fas fa-triangle-exclamation',
                'message' => $this->getDeprecationNotice(),
            ]);
        }

        return parent::configureResponseParameters($responseParameters);
    }
}
