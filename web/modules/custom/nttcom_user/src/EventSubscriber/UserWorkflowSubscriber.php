<?php

namespace Drupal\nttcom_user\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\argument\NullArgument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\user\Entity\User;

/**
 * Updates the current user's last access time.
 */
class UserWorkflowSubscriber implements EventSubscriberInterface {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\Core\Path\PathValidator definition.
   *
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $pathValidator;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new UserRequestSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   *
   */
  public function __construct(AccountInterface $account, EntityTypeManagerInterface $entity_type_manager,
                              RequestStack $requestStack, PathValidator $path_validator,
                              RouteMatchInterface $route_match) {
    $this->account = $account;
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $requestStack->getCurrentRequest();
    $this->pathValidator = $path_validator;
    $this->routeMatch = $route_match;
  }

  /**
   * Workflow the current user's login.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onGuideUpdateInfo(RequestEvent $event) {
    $url_object = $this->pathValidator->getUrlIfValid($this->request->getRequestUri());
    $redirect_url = NULL;
    if ($url_object !== FALSE) {
      $route_name = $url_object->getRouteName();
      $param = $this->request->query->all();
      $route_access = [
        'nttcom_change_pwd_page.change_password_form',
        'user.logout',
        'nttcom_user.guide_entry_form'
      ];
      $user = User::load($this->account->id());
      $roles = $user->getRoles();
      $approval = $user->get('field_approval_status')->value;

      if ($this->account->isAuthenticated() && !in_array($route_name, $route_access) &&
        in_array(ROLE_GUIDE, $roles) && !in_array(ROLE_SUPER_ADMIN, $roles)
        && in_array($approval,['new_account_guide','waiting_approval'])) {
          $redirect_url = Url::fromRoute('nttcom_user.guide_entry_form',[], ['absolute' => TRUE]);
      }
      if($this->account->isAuthenticated() && in_array($approval,['reject'])){
        user_logout();
        //$redirect_url = Url::fromRoute('<front>',[], ['absolute' => TRUE]);
      }
    }
    if (isset($redirect_url)) {
      $event->setResponse(new RedirectResponse($redirect_url->toString()));
    }

  }

  /**
   * Workflow the new user role guest register.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onGuestRegisterUser(RequestEvent $event) {
    $url_object = $this->pathValidator->getUrlIfValid($this->request->getRequestUri());
    $redirect_url = NULL;
    if ($url_object !== FALSE) {
      $route_name = $url_object->getRouteName();
      $param = $this->request->query->all();
      if ($this->account->isAnonymous() && $route_name == 'user.register') {
        if (empty($param['email_verification']) && empty($param['code_generation'])) {
          $redirect_url = Url::fromRoute('user.login',[], ['absolute' => TRUE]);
        }
      }
    }
    if (isset($redirect_url)) {
      $event->setResponse(new RedirectResponse($redirect_url->toString()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onGuideUpdateInfo', 100];
    $events[KernelEvents::REQUEST][] = ['onGuestRegisterUser', 99];
    return $events;
  }

}
