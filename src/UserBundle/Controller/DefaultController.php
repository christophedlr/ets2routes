<?php

/**
 * Copyright (C) 2018 Christophe Daloz - De Los Rios
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * The Software is provided “as is”, without warranty of any kind, express or implied, including but not
 * limited to the warranties of merchantability, fitness for a particular purpose and noninfringement.
 * In no event shall the authors or copyright holders be liable for any claim, damages or other liability,
 * whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software
 * or the use or other dealings in the Software.
 */

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use UserBundle\Entity\User;
use UserBundle\Forms\RegisterType;

class DefaultController extends Controller
{
    /**
     * @Route("/login", name="user_login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $requestStak = new RequestStack();
        $requestStak->push($request);

        $authenticationUtils = new AuthenticationUtils($requestStak);
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $this->addFlash('danger', $this->get('translator')->trans('login.invalid_credentials'));
        }

        return $this->render('user/login.html.twig', [
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="user_logout")
     * @throws \Exception
     */
    public function logout()
    {
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }

    /**
     * @Route("/register", name="user_register")
     * @Security("not has_role('ROLE_USER')")
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();

            $existUser = $this->getDoctrine()->getRepository(User::class)->findBy(['username' => $user->getUsername()]);

            if (!$existUser) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', $this->get('translator')->trans('register.success'));

                return $this->redirectToRoute('homepage');
            }

            $this->addFlash('danger', $this->get('translator')->trans('register.error'));
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
