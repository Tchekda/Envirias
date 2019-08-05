<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_SendmailTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('homepage');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, Swift_Mailer $mailer) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('homepage');
        }
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form['agreeTerms']->getData()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $form['plainPassword']->getData()
            ));
            $user->setToken(bin2hex(random_bytes(10)));
            $entityManager->persist($user);
            $entityManager->flush();
            if ($_SERVER['APP_ENV'] != 'dev') {
                $transport = new Swift_SendmailTransport();
                $mailer = new Swift_Mailer($transport);
            }
            $message = (new \Swift_Message('Envirias: Bienvenue'))
                ->setFrom('contact@envirias.com', 'Envirias')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/registration.html.twig',
                        ['user' => $user]
                    ),
                    'text/html'
                );
            $mailer->send($message);

            $this->addFlash('green', 'Un email vous a été envoyé afin de valider votre compte');
//            return $guardHandler->authenticateUserAndHandleSuccess(
//                $user,
//                $request,
//                $authenticator,
//                'main' // firewall name in security.yaml
//            );
            return $this->redirectToRoute('homepage');
        }
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/validate/{id}/{token}", name="user_validate", requirements={"id"="\d+"})
     */
    public function validate(User $user, string $token, UserRepository $userRepository, EntityManagerInterface $entityManager, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, Request $request) {
        if ($userRepository->findOneBy([
            'id' => $user->getId(),
            'token' => $token
        ])) {
            $user->setToken(null);
            $user->setValidated(true);
            $entityManager->flush();
            $this->addFlash('green', 'Votre compte a bien été validé!');
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        } else {
            $this->addFlash('red', 'Lien invalide, contactez un administrateur pour résoudre ce problème');
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/reset", name="password_reset")
     */
    public function resetPassword(Request $request, CsrfTokenManagerInterface $csrfTokenManager, UserRepository $userRepository, EntityManagerInterface $entityManager, Swift_Mailer $mailer) {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('homepage');
        }
        if ($request->getMethod() == 'POST') {
            if ($username = $request->get('username')) {
                $token = new CsrfToken('authenticate', $request->get('_csrf_token'));
                if ($csrfTokenManager->isTokenValid($token)) {
                    if ($user = $userRepository->findOneByLoginField($username)){
                        $user->setToken(bin2hex(random_bytes(10)));
                        $entityManager->flush();
                        if ($_SERVER['APP_ENV'] != 'dev') {
                            $transport = new Swift_SendmailTransport();
                            $mailer = new Swift_Mailer($transport);
                        }
                        $message = (new \Swift_Message('Envirias: Mot de Passe'))
                            ->setFrom('contact@envirias.com', 'Envirias')
                            ->setTo($user->getEmail())
                            ->setBody(
                                $this->renderView(
                                // templates/emails/registration.html.twig
                                    'emails/reset.html.twig',
                                    ['user' => $user]
                                ),
                                'text/html'
                            );
                        $mailer->send($message);
                        $this->addFlash('green', "Un email vous a été envoyé pour retrouver votre mot de passe");
                        return $this->redirectToRoute('homepage');
                    }else {
                        $this->addFlash('red', "Utilisateur introuvable");
                    }
                } else {
                    $this->addFlash('red', 'CSRF Invalide!');
                }
            } else {
                $this->addFlash('red', "Nom d'Utilisateur vide");
            }
        }
        return $this->render('security/reset.html.twig');
    }

    /**
     * @Route("password/{id}/{token}", name="password_new", requirements={"id"="\d+"})
     */
    public function newPassword(User $user, string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager){
        if ($this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('homepage');
        }
        if ($user->getToken()!= null and $token != $user->getToken()){
            $this->addFlash('red', 'Token invalide');
            return $this->redirectToRoute('homepage');
        }
        $form = $this->createForm(ResetPasswordFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $form['newPassword']->getData()
            ));
            $user->setToken(null);
            $entityManager->flush();
            $this->addFlash('green', 'Votre mot de passe à bien été mis à jour');
            return $this->redirectToRoute('login');
        }
        return $this->render('account/reset.html.twig', [
            'resetForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout() {
    }
}
