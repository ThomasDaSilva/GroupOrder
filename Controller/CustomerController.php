<?php

namespace GroupOrder\Controller;


use GroupOrder\Form\SubCustomerForm;
use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use GroupOrder\Service\GroupOrderService;
use ImageOptimizer\Exception\Exception;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Log\Tlog;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="group_order_customer_")
 */
class CustomerController extends BaseFrontController
{
    /**
     * @Route("/GroupOrder/SubCustomer/admin/CreateOrUpdate", name="admin_create")
     * @Route("/GroupOrder/SubCustomer/admin/CreateOrUpdate/{id}", name="admin_update")
     * @Route("/GroupOrder/SubCustomer/CreateOrUpdate/{id}", name="update")
     */
    public function createOrUpdateSubCustomerFromAdmin(
        RequestStack $requestStack,
        ParserContext $parserContext,
        MailerFactory $mailer,
        Translator $translator
    ){
        $subCustomerId = $requestStack->getCurrentRequest()->get('id');
        $mainCustomer = $requestStack->getCurrentRequest()->getSession()->get("CurrentUserIsMainCustomer");
        try {
            $formBase = $this->createForm(SubCustomerForm::getName());

            $form = $this->validateForm($formBase);
            $plainPassword = $form->get('password')->getData();
            $password = password_hash($plainPassword, PASSWORD_BCRYPT);

            $type = $form->get('type')->getData();

            $subCustomer = new GroupOrderSubCustomer();
            if ($subCustomerId) {
                $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($subCustomerId)->findOne();
            }

            $login = GroupOrderSubCustomerQuery::create()
                ->filterByLogin($form->get('login')->getData())
                ->filterByMainCustomerId($mainCustomer->getId())
                ->findOne();

            if ($login && $type != "update") {
                $form->get('login')->addError(new FormError($translator->trans("Identifiant ou email déjà utilisé dans ce groupe")));
                throw new FormValidationException($translator->trans("Identifiant ou email déjà utilisé dans ce groupe"));
            }

            $email = $form->get('login')->getData();

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailExist = GroupOrderSubCustomerQuery::create()
                    ->filterByEmail($email)
                    ->filterByMainCustomerId($mainCustomer->getId())
                    ->findOne();

                if ($emailExist && $type != "update") {
                    $form->get('login')->addError(new FormError($this->getTranslator()->trans("Email déjà utilisé dans ce groupe")));
                    throw new FormValidationException($this->getTranslator()->trans("Email déjà utilisé dans ce groupe"));
                }
            } else {
                $email = null;
            }

            $subCustomer
                ->setMainCustomerId($mainCustomer->getId())
                ->setFirstName($form->get('firstname')->getData())
                ->setLastName($form->get('lastname')->getData())
                ->setEmail($form->get('email')->getData())
                ->setAddress1($form->get('address1')->getData())
                ->setAddress2($form->get('address2')->getData())
                ->setAddress3($form->get('address3')->getData())
                ->setCity($form->get('city')->getData())
                ->setZipCode($form->get('zipcode')->getData())
                ->setCountryId($form->get('country_id')->getData())
                ->setLogin($form->get('login')->getData());

            if ($plainPassword) {
                $subCustomer->setPassword($password);
            }

            $subCustomer->save();

            if ($subCustomer->getEmail()) {
                $mailer->sendEmailMessage(GroupOrder::EMAIL_CREATE_SUB_CUTOMER_NAME,
                    [ConfigQuery::getStoreEmail() => ConfigQuery::getStoreName()],
                    [$subCustomer->getEmail() => $subCustomer->getFirstName() . ' ' . $subCustomer->getLastName()],
                    [
                        "firstname" => $subCustomer->getFirstName(),
                        "lastname" => $subCustomer->getLastName(),
                        "code" => $mainCustomer->getCode(),
                        "login" => $subCustomer->getLogin()
                    ]
                );
            }

            return $this->generateSuccessRedirect($formBase);

        } catch (\Exception $exception) {
            $formBase
                ->setError(1)
                ->setErrorMessage($exception->getMessage());

            $parserContext
                ->setGeneralError($exception->getMessage())
                ->addForm($formBase);

            if ($exception->getMessage() && $formBase->hasErrorUrl()) {
                return $this->generateErrorRedirect($formBase);
            }
        }
        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/account"));
    }

    /**
     * @Route("/GroupOrder/SubCustomer/register", name="register")
     */
    public function createOrUpdateSubCustomer(
        RequestStack $requestStack,
        ParserContext $parserContext,
        Translator $translator
    ){
        try {
            $formBase = $this->createForm("group_order_sub_customer_register_form");
            $form = $this->validateForm($formBase);

            $plainPassword = $form->get('password')->getData();
            $code = $form->get('group_code')->getData();

            $password = password_hash($plainPassword, PASSWORD_BCRYPT);

            $mainCustomer = GroupOrderMainCustomerQuery::create()->filterByCode($code)->findOne();

            if (null == $mainCustomer) {
                throw new \Exception('Groupe code incorrecte');
            }

            $subCustomer = new GroupOrderSubCustomer();

            $login = GroupOrderSubCustomerQuery::create()
                ->filterByLogin($form->get('login')->getData())
                ->filterByMainCustomerId($mainCustomer->getId())
                ->findOne();

            if ($login) {
                throw new FormValidationException($translator->trans("Identifiant ou email existant dans ce groupe"));
            }

            $email = $form->get('login')->getData();

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailExist = GroupOrderSubCustomerQuery::create()
                    ->filterByEmail($email)
                    ->filterByMainCustomerId($mainCustomer->getId())
                    ->findOne();

                if ($emailExist) {
                    throw new FormValidationException($translator->trans("Identifiant ou email existant dans ce groupe"));
                }
            } else {
                $email = null;
            }

            $subCustomer
                ->setMainCustomerId($mainCustomer->getId())
                ->setFirstName($form->get('firstname')->getData())
                ->setLastName($form->get('lastname')->getData())
                ->setEmail($email)
                ->setAddress1($form->get('address1')->getData())
                ->setAddress2($form->get('address2')->getData())
                ->setAddress3($form->get('address3')->getData())
                ->setCity($form->get('city')->getData())
                ->setZipCode($form->get('zipcode')->getData())
                ->setCountryId($form->get('country_id')->getData())
                ->setLogin($form->get('login')->getData());

            if ($plainPassword) {
                $subCustomer->setPassword($password);
            }

            $subCustomer->save();

            $requestStack->getCurrentRequest()->getSession()->set("SubCustomerSessionCart", true);
            $requestStack->getCurrentRequest()->getSession()->set("GroupOrderLoginSubCustomer", $subCustomer->getId());
            $requestStack->getCurrentRequest()->getSession()->set("GroupOrderMainCustomer", $mainCustomer->getId());

            return $this->generateSuccessRedirect($formBase);

        } catch (\Exception $exception) {
            $formBase->setErrorMessage($exception->getMessage());

            $parserContext
                ->setGeneralError($exception->getMessage())
                ->addForm($formBase);

            if ($exception->getMessage() && $formBase->hasErrorUrl()) {
                return $this->generateErrorRedirect($formBase);
            }
        }
    }

    /**
     * @Route("/GroupOrder/SubCustomer/delete", name="delete")
     */
    public function deleteSubCustomer()
    {
        $deleteForm = $this->createForm('group_order_sub_customer_delete');

        try {
            $form = $this->validateForm($deleteForm);

            $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($form->get('sub_customer_id')->getData())->findOne();
            if ($subCustomer !== null){
                $subCustomer->delete();
            }

        } catch (Exception $exception) {
            Tlog::getInstance()->addError($exception->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/account-cse'));
    }

    /**
     * @Route("/module/groupOrder/getSubCustomerCart", name="get_sub_customer_cart")
     * @return RedirectResponse|Response
     * @throws PropelException
     */
    public function getSubCustomerCart(RequestStack $requestStack, GroupOrderService $service)
    {
        $subCustomerId = $requestStack->getCurrentRequest()->get('sub_customer_id', null);

        if ($subCustomerId === null) {
            return
                $this->generateRedirect(
                    URL::getInstance()->absoluteUrl("/account")
                );
        }

        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);

        $cartItems = $service->getSubCustomerTaxedCart($subCustomerId);

        $response = [
            "cartItems" => $cartItems
        ];

        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', $subCustomerId);

        return $this->jsonResponse(json_encode($response));
    }

    /**
     * @Route("/module/groupOrder/goHome", name="go_home")
     */
    public function goHome(RequestStack $requestStack)
    {
        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);
    }
}