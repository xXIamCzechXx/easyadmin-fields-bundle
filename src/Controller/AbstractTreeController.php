<?php

declare(strict_types=1);

namespace Iamczech\EasyAdminFieldsBundle\Controller;

use App\Controller\Admin\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

abstract class AbstractTreeController extends AbstractCrudController
{
    abstract public static function getEntityFqcn(): string;

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addCssFile('@iamczech/easyadmin-fields/styles/tree.css');
    }

    #[Route('/_easyadmin-fields-bundle/tree/reorder', name: 'iamczech_easyadminfields_tree_reorder', methods: ['POST'])]
    public function reorder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $entityManager = $this->container->get('doctrine')->getManagerForClass($data['class']);

        /** @var NestedTreeRepository $repo */
        $repo = $entityManager->getRepository($data['class']);
        if (!$repo instanceof NestedTreeRepository) {
            return new JsonResponse(['success' => false, 'error' => 'Entity is not a Gedmo Tree'], 400);
        }
        if (!$data || empty($data['id']) || !array_key_exists('parent', $data) || !array_key_exists('prev', $data) || !array_key_exists('next', $data)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid payload –> id, parent, prev or next is missing in request'], 400);
        }
        if (!$node = $repo->find((int)$data['id'])) {
            return new JsonResponse(['success' => false, 'error' => 'Node not found'], 404);
        }

        try {
            if ($data['toRoot']) {
                $node->setParent(null);
                $entityManager->persist($node);
            } else {
                if ($previous = $repo->find($data['prev'] ?? 0)) {
                    $repo->persistAsNextSiblingOf($node, $previous);
                } elseif ($next = $repo->find($data['next'] ?? 0)) {
                    $repo->persistAsPrevSiblingOf($node, $next);
                } elseif ($parent = $repo->find($data['parent'] ?? 0)) {
                    $repo->persistAsFirstChildOf($node, $parent);
                } else {
                    return new JsonResponse(['success' => false, 'error' => 'Unable to determine position, check ajax payload']);
                }
            }

            if ($data['toRoot'] || $data['fromRoot']) {
                $roots = $repo->findBy(['parent' => null], ['sequence' => 'ASC', 'id' => 'ASC']);
                $filtered = array_filter($roots, fn($r) => $r->getId() !== $node->getId());
                $ordered = array_values($filtered);

                array_splice($ordered, (int)($data['newIndex'] ?? count($ordered)), 0, [$node]);

                $sequence = 1;
                foreach ($ordered as $root) {
                    $root->setSequence($sequence++);
                    $entityManager->persist($root);
                }
            }
            $entityManager->flush();

            $table = $entityManager->getClassMetadata($data['class'])->getTableName();
            $entityManager->getConnection()->executeStatement("
                UPDATE {$table} AS child JOIN {$table} AS root ON child.root = root.id
                SET child.sequence = root.sequence WHERE root.parent_id IS NULL AND root.sequence IS NOT NULL
            ");

            return new JsonResponse(['success' => true]);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
