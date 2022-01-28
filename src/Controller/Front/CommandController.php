<?php

namespace App\Controller\Front;

use App\Entity\Command;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\CommandRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommandController extends AbstractController
{
    /**
     * @Route("cart/add/{id}", name="add_cart")
     */
    public function addCart($id, SessionInterface $sessionInterface)
    {
        $cart = $sessionInterface->get('cart', []);
        if(!empty($cart[$id])){
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute('show_product', ['id' => $id]);
    }

    /**
     * @Route("cart", name="show_cart")
     */
    public function showCart(SessionInterface $sessionInterface, ProductRepository $productRepository)
    {
        $cart = $sessionInterface->get('cart', []);
        $cartWithData = [];

        foreach($cart as $id => $quantity){
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'questity' => $quantity
            ];
        }

        return $this->render('front/cart.html.twig', ['cartProducts' => $cartWithData]);
    }

    /**
     * @Route("cart/delete/{id}", name="delete_cart")
     */
    public function deleteCart($id, SessionInterface $sessionInterface)
    {
        $cart = $sessionInterface->get('cart', []);

        if(!empty($cart[$id] && $cart[$id] == 1)) {
            unset($cart[$id]);
        } else {
            $cart[$id]--;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute('show_cart');
    }

    /**
     * @Route("cart/infos", name="cart_infos")
     */
    public function carInfos(UserRepository $userRepository)
    {
        $user = $this->getUser();

        if ($user) {
            $user_email = $user->getUserIdentifier();
            $user_true = $userRepository->findOneBy(['email' => $user_email]);
            return $this->render('front/infoscart.html.twig', ['user' => $user_true]);
        } else {
            return $this->render('front/infoscart.html.twig');
        }
    }

    /**
     * @Route("command/create", name="command_create")
     */
    public function commandCreate(
        CommandRepository $commandRepository, 
        SessionInterface $sessionInterface,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        MailerInterface $mailerInterface,
        Request $request
    ){
        $command = new Command();

        $commands = $commandRepository->findAll();
        $number = count($commands);
        $command_number = $number + 1;

        $command->setNumber("Command-" . $command_number);
        $command->setDate(new \DateTime("NOW"));

        $cart = $sessionInterface->get('cart', []);
        $price = 0;

        foreach ($cart as $id_product => $quantity) {

            $product = $productRepository->find($id_product);
            $price_product = $$product->getPrice();
            $price = $price + ($price_product * $quantity);
            $product_stock = $product->getStock();
            $product_stock_final = $product_stock - $quantity;
            $product->setStock($product_stock_final);
            $command->addProduct($product);
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();
            unset($cart[$id_product]);
            $sessionInterface->set('cart', $cart);
            
        }

        $command->setPrice($price);

        $user = $this->getUser();

        if($user){

            $user_email = $user->getUserIdentifier();
            $user_true = $userRepository->findOneBy(['email' => $user_email]);

            $command->setUser($user_true);

            $email = (new Email())
                ->from('admin@test.com')
                ->to($user_email)
                ->subject('Commande')
                ->html('<p>Commande de ' . $price . '€</p>');

            $mailerInterface->send($email);

        } else {

            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $address = $request->request->get('address');
            $zipcode = $request->request->get('zipcode');
            $city = $request->request->get('city');

            $command->setName($name);
            $command->setEmail($email);
            $command->setAddress($address);
            $command->setZipcode($zipcode);
            $command->setCity($city);

            $email = (new Email())
                ->from('admin@test.com')
                ->to($email)
                ->subject('Commande')
                ->html('<p>Commande de ' . $price . '€</p>');

            $mailerInterface->send($email);

        }

        $entityManagerInterface->persist($command);
        $entityManagerInterface->flush();

        return $this->redirectToRoute('front_home');
    }
}