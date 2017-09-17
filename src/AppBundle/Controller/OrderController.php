<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrderController extends Controller
{
    /**
    * @Route("/", name="homepage")
    */
    public function orderAction(Request $request)
    {
        $order = new Order();
        $form = $this->createFormBuilder($order, ['attr'=>['novalidate'=>'novalidate']])
            ->add('name', TextType::class, array('label' => 'Vardas'))
            ->add('lastName', TextType::class, array('label' => 'Pavardė'))
            ->add('address', TextType::class, array('label' => 'Adresas'))
            ->add('phone', TextType::class, array('label' => 'Telefono numeris'))
            ->add('save', SubmitType::class, array('label' => 'Užsisakyti'))
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            $validator = $this->get('validator');
            $order = $form->getData();
            $errors = $validator->validate($order);
            if(count($errors) == 0)
            {
                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();

                return $this->redirect('/completed');//darom redirect, kad ištrintume formos duomenis
        }
            //jei rastą klaidų - grąžinam tą pačią formą su klaidomis
            else
                return $this->render('order.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('order.html.twig', array(
            'form' => $form->createView(),
            'errors' => array(),
        ));
    }

    /**
     * @Route("/completed", name="order_completed")
     */
    public function orderCompleteAction()
    {
        return $this->render('completed.html.twig');
    }
}