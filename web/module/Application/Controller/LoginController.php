<?php

namespace Application\Controller;

use Application\Exception\ValidacaoException;
use Application\Helper\Output;
use Application\Model\UsuarioModel;
use Application\View\ApplicationView;
use Szy\Http\Request;
use Szy\Mvc\Controller\AbstractController;
use Szy\Mvc\View\View;

class LoginController extends AbstractController
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        sleep(2);
    }

    public function testeAction()
    {
        $model = new UsuarioModel();
        $r = $model->query("SELECT * FROM usuario WHERE email = ? AND senha = ?", array("edvaldoszy@gmail.com", md5("123")));
        var_dump($r->first());
    }

    /**
     * @param string $mobile
     * @return View
     */
    public function indexAction($mobile = null)
    {
        $view = new ApplicationView($this->response, "login/index");
        $output = new Output();

        if ($this->isMethod(Request::METHOD_POST)) {
            $email = $this->request->getPost("email", FILTER_SANITIZE_EMAIL);
            $senha = $this->request->getPost("senha", FILTER_SANITIZE_STRING);

            try {
                $usuario = $this->validar($email, $senha);
                $this->getSession()->write("usuario", $usuario);

                if (!empty($mobile)) {
                    $output->status = "OK";
                    $output->data = $usuario;
                    $this->response->write($output);
                    return;
                }

                $this->response->sendRedirect("/admin");
            } catch (ValidacaoException $ex) {
                if (!empty($mobile)) {
                    $output->message = $ex->getMessage();
                    $this->response->write($output);
                    return;
                }

                $view->setAttribute("message", $ex->getMessage());
            }
        }
        $view->flush();
    }

    /**
     * @param string $email
     * @param string $senha
     * @return \Szy\Database\Record
     * @throws \Exception
     */
    private function validar($email, $senha)
    {
        if (empty($email) || empty($senha))
            throw new ValidacaoException("Preencha os dois campos");

        $model = new UsuarioModel();
        $res = $model->query("SELECT codigo, nome, email, admin FROM usuario WHERE email = ? AND senha = ? LIMIT 1", array($email, md5($senha)));
        if ($res->count() < 1)
            throw new ValidacaoException("Dados de acesso inválidos");

        return $res->first();
    }
}