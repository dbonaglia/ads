<?php

namespace App\Controller;

use App\Model\User;
use App\Util\SessionManager;

class AuthController extends Controller
{
    public function registerView()
    {
        $this->render('register', [
            'page_title' => 'Inscription'
        ]);
    }

    public function registerTreatment()
    {
        $user = new User(
            username: strip_tags($_POST['inputUsername']),
            email: strip_tags($_POST['inputEmail']),
            password: strip_tags($_POST['inputPassword'])
        );
        if ($user->insert()) {
            $this->redirect('/connexion');
        } else {
            $this->redirect('/inscription');
        }
    }

    public function loginView()
    {
        $this->render('login', [
            'page_title' => 'Connexion'
        ]);
    }

    public function loginTreatment()
    {
        if (SessionManager::connectUser(strip_tags($_POST['inputEmail']), strip_tags($_POST['inputPassword']))) {
            $this->redirect('/');
        } else {
            $this->redirect('/connexion');
        }
    }

    public function logout()
    {
        SessionManager::disconnectUser();
        $this->redirect('/');
    }

    public function profileView()
    {
        $this->render('profile', [
            'page_title' => 'Profil'
        ]);
    }

    public function profileTreatment()
    {
        if (!empty(strip_tags($_POST['inputActualPassword']))) {
            if (password_verify(strip_tags($_POST['inputActualPassword']), $this->connectedUser->getPassword())) {
                // Si l'utilisateur souhaite modifier son mot de passe
                if ($_POST['inputNewPassword'] !== '' || $_POST['inputNewPasswordConfirm'] !== '') {
                    if ($_POST['inputNewPassword'] == $_POST['inputNewPasswordConfirm']) {
                        $this->connectedUser->setPassword($_POST['inputNewPassword']);
                        SessionManager::addFlashMessage("Votre mot de passe a été modifié avec succès. Vous pouvez dès à présent vous reconnecter avec celui ci.", 'success');
                    } else {
                        SessionManager::addFlashMessage("Vos nouveaux mots de passe ne correspondent pas.", 'warning');
                    }
                } else {
                    // On redéfinit le mot de passe actuel car à l'envoit de la requête SQL le mdp va de nouveau être hashé
                    $this->connectedUser->setPassword($_POST['inputActualPassword']);
                }

                if ($_POST['inputUsername']) {
                    $user->setFirstname($_POST['inputUsername']);
                }
                if ($_POST['inputFirstname']) {
                    $user->setFirstname($_POST['inputFirstname']);
                }
                if ($_POST['inputLastname']) {
                    $user->setLastname($_POST['inputLastname']);
                }
                if ($_POST['inputPostalCode']) {
                    $user->setPostalCode($_POST['inputPostalCode']);
                }
                if ($_POST['inputCity']) {
                    $user->setCity($_POST['inputCity']);
                }
                if ($_POST['inputStreet']) {
                    $user->setStreet($_POST['inputStreet']);
                }
                $user->update();
                SessionManager::addFlashMessage("Votre compte à correctement été modifié.", 'success');
                $this->redirect('/profil');
            } else {
                SessionManager::addFlashMessage("Votre mot de passe actuel est incorrect.", 'warning');
                $this->render('profile', [
                    'page_title' => 'Profil'
                ]);
            }
        } else {
            SessionManager::addFlashMessage("Vous n'avez pas renseigné votre mot de passe actuel.", 'warning');
            $this->render('profile', [
                'page_title' => 'Profil'
            ]);
        }
    }
}