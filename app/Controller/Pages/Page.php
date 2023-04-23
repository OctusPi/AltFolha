<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Utils\Html;
use Octus\App\Utils\View;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Data\FactoryDao;

abstract class Page implements ItfPage
{
    protected int $profile;
    protected int $level;
    protected Session $session;
    protected ?EntityUsuario $usuario;
    protected ?EntityCompany $company;


    public function __construct(Session $session, ?EntityUsuario $usuario = null, bool $secutiry = true, int $profile = 0, int $level = 0)
    {
        $this->profile = $profile;
        $this->level   = $level;
        $this->session = $session;
        $this->usuario = $usuario;
        $this->company = $this->getCompany();

        //checks access security required
        Security::guardian($this, $this->session, $this->usuario, $secutiry);
    }

    /**
     * Method return credentials to grant access page
     *
     * @return array
     */
    public function getCredentials():array
    {
        return [
            'profile' => $this->profile,
            'level'   => $this->level
        ];
    }

    /**
     * Method return infos system name and description in database
     *
     * @return EntityCompany|null
     */
    private function getCompany():EntityCompany
    {
        $daoCompany = (new FactoryDao())->daoCompany();
        $info    = $daoCompany->readData();
        return $info != null ? $info : new EntityCompany();
    }

    /**
     * Method return render html with page request by route url
     *
     * @param string $title
     * @param string $content
     * @param array $params
     * @param bool $secutiry
     * @param bool $hshow
     * @param bool $fshow
     * @return string
     */
    public function getPage(string $title, string $content, array $params = [], bool $hshow = true, bool $fshow = true):string
    {
        //set token exclusive request page
        Forms::setToken();

        $base = [
            'title'      => Utils::attr('sistema', $this->company).' '.$title,
            'header'     => $this->getHeader($hshow),
            'footer'     => $this->getFooter($fshow),
            'content'    => View::renderView($content, $params),
            'modal'      => View::renderView('fragments/forms/delete'),
            
            'syscopy'    => "Prefeitura Municipal de Araripe <br> Departamento de Tecnologia &copy 2023",
            'sisname'    => Utils::attr('sistema',   $this->company),
            'sisdesc'    => Utils::attr('descricao', $this->company),

            'token'      => Forms::getToken(),
            'action_del' => Route::route(['action' => 'send'])
        ];

        return View::renderView('pages/default', $base);
    }

    private function getHeader(bool $hshow = true):string
    {   

        $isAdm = ($this->usuario != null && $this->usuario->getAttr('perfil') == 1);

        $params  = [
            'sys_version'    => 'v0.1.2',
            'user_name'      => $this->usuario != null ? explode(' ', Utils::attr('nome', $this->usuario))[0] : '',
            'user_perfil'    => $this->usuario != null ? Utils::at($this->usuario->getAttr('perfil'), EntityUsuario::getPerfilArr()) : '',
            'user_lastlogin' => Dates::fmttDateTimeView(Utils::attr('lastlogin', $this->usuario)),
            'user_adm'       => $isAdm ? '' : 'd-none', 
            'nav_router'     => Html::buildNavRoter($this->usuario)
        ];
        return $hshow ? View::renderView('fragments/header', $params) : '';
    }

    public function getFooter(bool $fshow = true):string
    {
        return $fshow ? View::renderView('fragments/footer') : '';
    }
}