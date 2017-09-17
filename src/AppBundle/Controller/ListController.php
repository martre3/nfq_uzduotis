<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

class ListController extends Controller
{
    /**
     * @Route("/list", name="show")
     */
    public function defaultShowAction()
    {
        $_SESSION['pattern'] = '';
        $_SESSION['sort'] = 0;
        return $this->redirect('/list/1');
    }

    /**
     * @Route("/list/{page}", name="show_list", requirements={"page": "\d+"})
     */
    public function showAction($page = 1, Request $request)
    {
        $ordersPerPage = 10;//kiek kiekvienam puslapyje bus užsakymų eilučių
        $buttonCount = 4;//kiek nuodrodų į kitus puslapius matysime
        $formData = (object) [
            'pattern' => '',
            'sort' => 0
        ];
        $form = $this->getForm();//sukuriame paieškos laukelio ir rikiavimo formą
        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $page = 1;//jei rikiuojame ar iekšome grąžiname vartotoją į primą sąrašo puslapį
            $_SESSION['pattern'] = $formData->pattern = (string)$form->get('searchPattern')->GetData();
            //išsaugojame įvestus duomenis į sesija, kad nedingtų kai keičiame sąrašo puslapį
            $_SESSION['sort'] = $formData->sort = $form->get('sort')->GetData();
        }
        else if(isset($_SESSION['pattern'])){//jei pakeitėme puslapį susigražinam paieškos ir rikiavimo duomenis
            $formData->pattern = $_SESSION['pattern'];
            $formData->sort = $_SESSION['sort'];
            $form->get('searchPattern')->SetData($formData->pattern);
            $form->get('sort')->SetData($formData->sort);
        }

        $data = $this->getResults($formData, $page, $ordersPerPage);//grąžiną iš duomenų bazės kiek išviso puslapių ir vieną puslapo užsakymų sąrašą
        $pages = $data->totalCount;
        $orders = $data->results;
        $page = $data->page;

        $buttons = $this->getButtons($pages, $buttonCount, $page);//grąžina sukurtus puslapiavimo mygtukus

        return $this->render('/list.html.twig', array(
            'form' => $form->createView(),
            'current' => $page,
            'orders' => $orders,
            'first' => $buttons->first,
            'last' => $buttons->last));
    }

    //sukuriam formą
    //kiekvienam rikiavimo pasirinkimui priskiriam numerį
    function getForm()
    {
        $filter = new Filter();
        return $this->createFormBuilder($filter, ['attr'=>['novalidate'=>'novalidate']])
            ->add('searchPattern', TextType::class, array('label' => 'Paieška: '))
            ->add('sort', ChoiceType::class, array('label' => 'Rikiuoti: ',
                'choices' => array('Numatytasis' => 0, 'Vardai A->Z' => 1, 'Vardai Z->A' => 2,
                    'Pavardės A->Z' => 3, 'Pavardės Z->A' => 4, 'Adresai A->Z' => 5,
                    'Adresai Z->A' => 6, 'Numeriai mažėjimo tvarka' => 8, 'Numeriai didėjimo tvarka' => 7)))
            ->add('save', SubmitType::class, array('label' => 'Vykdyti'))
            ->getForm();
    }

    //grąžiną duomenis iš duomenų bazės
    function getResults($form, $page, $ordersPerPage)
    {
        if($page <= 0)//jei į url buvo įvestas per mažas skaičius, gražinam pirmo puslapio sąrašą
            $page = 1;
        $returns = (object) [
            'totalCount' => 0,
            'results' => array(),
            'page' => $page
        ];
        $sortName = 'id';//pagal nutylėjimą rušiuosim pagal 'id'
        $repository = $this->getDoctrine()
            ->getRepository(Order::class);
        $dir = $form->sort % 2 == 0 ? 'DESC' : 'ASC';//iš pasirinkimų lentėles pasirinkimui priskirto numerio nusprendžiam rinkiavimo tvarką


        switch (ceil($form->sort/ 2))//iš pasirinkimų lentelės pasirinkimui priskirto numerio nusprendžiam pagal ką rikiuosim
        {
            case 1:
                $sortName = 'name';
                break;
            case 2:
                $sortName = 'lastName';
                break;
            case 3:
                $sortName = 'address';
                break;
            case 4:
                $sortName = 'phone';
                break;
        }

        $qb = $repository->createQueryBuilder('p')
            ->where('p.name LIKE :pattern OR p.lastName LIKE :pattern OR p.address LIKE :pattern 
            OR p.phone LIKE :pattern')
            ->setParameter('pattern', '%'.$form->pattern.'%');//ieškome kas atitinka mūsų ivestą raktažodį
        $returns->totalCount = ceil(count($qb->getQuery()->execute())/$ordersPerPage);//viską grąžiname, suskaičiuojame kiek grąžinta eilučių ir paskaičiuojame kiek tai bus puslapių
        $returns->totalCount = $returns->totalCount <= 0?1:$returns->totalCount;//jei nieko nerasta nustatome puslapių skaičių į vienetą, kad išvengtume klaidų
        if($page > $returns->totalCount)//jeigu buvo įvestas didesnis puslapio skaičius negu jų yra nustatome dabartinį puslapį į paskutinį
            $returns->page = $returns->totalCount;

        $returns->results = $qb
            ->addOrderBy($qb->getRootAlias() . '.'.$sortName, $dir)//rikiuojame pagal turimą raktą ir kryptį
            ->setFirstResult(($returns->page - 1) * $ordersPerPage)//nustatome nuo kurios eilutes turi grąžinti
            ->setMaxResults($ordersPerPage)//kiek eilučių turi grąžinti daugiausia
            ->getQuery()
            ->execute();
        return $returns;
    }

    //nustato nuo kurio iki kurio puslapiavimo nuorodų mygtukų rodyti
    function getButtons($pages, $buttonCount, $page)
    {
        $data = (object) [
            'first' => 1,
            'last' => $pages
        ];//pagal nutylėjimą rodysim nuo pirmo iki paskutinio

        if($pages > $buttonCount) {//jei puslapių yra daugiau nei leidžiamo mygtukų skaičiaus - apskaičiuojame kuriuos rodysime
            $data->first = $page - floor($buttonCount / 2) > 1 ? $page - floor($buttonCount / 2) : 1;
            $data->last = $pages >= $data->first + $buttonCount ? $data->first + $buttonCount - 1 : $pages;
            if ($data->last - $data->first < $buttonCount && $data->last > $buttonCount)//jeigu priėjome pabaigą pirmo mygtuko nebeslenkame
                $data->first = $data->last - $buttonCount + 1;
        }
        return $data;
    }
}