<?php

namespace App\Controller;

use App\Entity\User;
use App\Helper\CreateUserValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserController extends AbstractController
{
    /** @var User $user */
    protected $user;

    /** @var string $error */
    protected $error;

    /** @var SessionInterface $session */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->loadUserFromSession();
    }

    /**
     *
     */
    protected function loadUserFromSession(): void
    {
        $this->user = $this->session->get('user');
    }

    /**
     * @Route("/", name="start")
     * @return Response
     */
    public function start(): Response
    {
        if($this->user !== null) {
            var_dump($this->user);
            return $this->render('user/index.html.twig', ['user' => $this->user]);
        }

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        if($this->user !== null) {
            return $this->redirectToRoute('start');
        }

        if($request->get('submit') === null) {
            return $this->render('user/login.html.twig');
        }

        $username = $request->get('username');
        $password = $request->get('password');

        try {
            $this->loadUser($username, $password);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }

        if($this->error) {
            return $this->render('user/login.html.twig', ['username' => $username, 'error' => $this->error]);
        }
        $this->session->set('user', $this->user);
        return $this->redirectToRoute('start');
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function loadUser(string $username, string $password): void
    {
        $this->user = $this->findUserByName($username);

        if($this->user === null) {
            throw new \RuntimeException('Error: user not found!');
        }

        if($this->user->getPassword() !== md5($password)) {
            throw new \RuntimeException('Error: password not correct!');
        }
    }

    /**
     * @param string $username
     * @return object|null
     */
    protected function findUserByName(string $username)
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        return $userRepository->findOneBy(['username' => $username]);
    }

    /**
     * @Route("/logout", name="logout")
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $this->session->remove('user');
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        if($request->get('submit') === null) {
            return $this->render('user/create.html.twig');
        }

        $username = $request->get('username');
        $password1 = $request->get('password1');
        $password2 = $request->get('password2');

        try {
            $this->checkCreateData($username, $password1, $password2);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }

        if($this->error) {
            return $this->render('user/create.html.twig', ['username' => $username, 'error' => $this->error]);
        }

        $this->doCreate($username, $password1);
        return $this->render('user/message.html.twig', ['message' => 'User ' . $username . ' created, please sign in.']);
    }

    /**
     * @param string $username
     * @param string $password1
     * @param string $password2
     */
    protected function checkCreateData(string $username, string $password1, string $password2): void
    {
        $validator = new CreateUserValidator();
        $validator->notEmpty($username, 'Error: username is empty!');

        if($this->findUserByName($username) !== null) {
            throw new \RuntimeException('Error: User ' . $username . ' already exists!');
        }

        $validator->notEmpty($password1, 'Error: password is empty!');

        $validator->same($password1, $password2, 'Error: passwords are not same!');
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function doCreate(string $username, string $password): void
    {
        $userManager = $this->getDoctrine()->getManager();

        $user = (new User())->setUsername($username)
                            ->setPassword(md5($password));
        $userManager->persist($user);
        $userManager->flush();
    }
}
