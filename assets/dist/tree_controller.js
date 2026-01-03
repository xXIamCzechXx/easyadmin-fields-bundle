import { Controller } from "@hotwired/stimulus";
import Sortable from 'sortablejs'

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_tree = class extends Controller {

    /**
     * Documentation: https://github.com/SortableJS/Sortable
     */
    connect() {
        this.initTree(this.element);

        this.element.querySelectorAll('ul').forEach((ul) => {
            this.initTree(ul); // recursive init trees of all existing <ul>
        })
    }

    initTree(ul) {
        if (ul.dataset.sortableInitialized) {
            return; // each list could be initialized only once
        }
        ul.dataset.sortableInitialized = 'true'

        Sortable.create(ul, {
            group: {
                name: 'nested-tree',
                pull: true,
                put: true,
            },
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.3,
            handle: '.drag-handle',
            draggable: '.tree-item',
            ghostClass: 'drag-ghost',
            onEnd: (event) => this.onEnd(event),
        })
    }

    onEnd(e) {
        fetch(`/_easyadmin-fields-bundle/tree/reorder`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify({
                id: e.item.dataset.id,
                class: e.item.dataset.class,
                parent: e.to.closest('.tree-item')?.dataset.id ?? null,
                fromRoot: e.from.dataset.root === 'true',
                toRoot: e.to.dataset.root === 'true',
                oldIndex: e.oldIndex,
                newIndex: e.newIndex,
                prev: e.newIndex > 0 ? e.to.children[e.newIndex - 1]?.dataset.id ?? null : null,
                next: e.newIndex < e.to.children.length - 1 ? e.to.children[e.newIndex + 1]?.dataset.id ?? null : null,
            }),
        }).then(r => []);
    }
}

export {
    controller_tree as default
};
