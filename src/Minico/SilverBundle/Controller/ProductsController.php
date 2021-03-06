<?php

namespace Minico\SilverBundle\Controller;

use Minico\SilverBundle\Entity\Entries;
use Minico\SilverBundle\Entity\Transfer;
use Minico\SilverBundle\Form\EntriesType;
use Minico\SilverBundle\Form\ProductAndOffsetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Minico\SilverBundle\Entity\Products;
use Minico\SilverBundle\Form\ProductsType;

/**
 * Products controller.
 *
 * @Route("/products")
 */
class ProductsController extends Controller
{

    /**
     * Lists all Products entities.
     *
     * @Route("/", name="products")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = array();
        $offset = 0;
        $form = $this->createForm(new ProductAndOffsetType(), null, array(
            'action' => $this->generateUrl('products'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Filter',));

        if ($request->isMethod('POST')) {
            $formPostData = $request->request->get('productAndOffset', array());
            if (!empty($formPostData["code"])) {
                $filter = array(
                    'productCode' => $formPostData["code"]
                );
            }

            if (!empty($formPostData["offset"])) {
                $offset = $formPostData["offset"];
            }
        }

        $entities = $em->getRepository('MinicoSilverBundle:Products')->findBy(
            $filter,
            array(),
            100,
            $offset
        );

        return array(
            'entities' => $entities,
            'form' => $form->createView(),
        );
    }
    /**
     * Creates a new Products entity.
     *
     * @Route("/", name="products_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Products:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Products();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('products_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Products entity.
    *
    * @param Products $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Products $entity)
    {
        $form = $this->createForm(new ProductsType(), $entity, array(
            'action' => $this->generateUrl('products_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Products entity.
     *
     * @Route("/new", name="products_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Products();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Products entity.
     *
     * @Route("/{id}", name="products_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Products $entity */
        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $entries_form = $this
            ->createForm(
                new EntriesType($em, $entity->getId()),
                null,
                array(
                    'action' => $this->generateUrl('entries_create')

                )
            );

        $entriesShow = false;
        /** @var Entries[] $entries */
        $entries = $entity->getEntries();
        if (count($entries)) {
            $entriesShow = true;
        }
        $transfersShow = false;
        /** @var Transfer[] $transfers */
        $transfers = $entity->getTransfers();
        if (count($transfers)) {
            $transfersShow = true;
        }

        return array(
            'entity'      => $entity,
            'entries_form' => $entries_form->createView(),
            'delete_form' => $deleteForm->createView(),
            'entriesShow' => $entriesShow,
            'transfersShow' => $transfersShow,
            'entries' => $entries,
            'transfers' => $transfers,
        );
    }

    /**
     * Displays a form to edit an existing Products entity.
     *
     * @Route("/{id}/edit", name="products_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Products entity.
    *
    * @param Products $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Products $entity)
    {
        $form = $this->createForm(new ProductsType(), $entity, array(
            'action' => $this->generateUrl('products_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Products entity.
     *
     * @Route("/{id}", name="products_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Products:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('products_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Products entity.
     *
     * @Route("/{id}", name="products_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Products entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('products'));
    }

    /**
     * Creates a form to delete a Products entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('products_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
