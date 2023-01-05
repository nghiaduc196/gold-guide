<?php

namespace Drupal\nttcom_user\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;

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
  public function onWorkflowUser(RequestEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    $redirect_url = NULL;
    if ($this->account->isAuthenticated()) {
      if (in_array(ROLE_GUIDE, $this->account->getRoles())) {
      
      }
      
      switch ($route_name) {
        case 'user.login';
          // Redirect an authenticated user to the profile page.
          $redirect_url = Url::fromRoute('entity.user.canonical', ['user' => $this->account->id()], ['absolute' => TRUE]);
          break;

        case 'user.register';
          // Redirect an authenticated user to the profile form.
          $redirect_url = Url::fromRoute('entity.user.edit_form', ['user' => $this->account->id()], ['absolute' => TRUE]);
          break;
      }
    }
    elseif ($route_name === 'user.page') {
      $redirect_url = Url::fromRoute('user.login', [], ['absolute' => TRUE]);
    }
    elseif ($route_name === 'user.logout') {
      $redirect_url = Url::fromRoute('<front>', [], ['absolute' => TRUE]);
    }

    if ($redirect_url) {
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
    $events[KernelEvents::REQUEST][] = ['onWorkflowUser', 100];
    $events[KernelEvents::REQUEST][] = ['onGuestRegisterUser', 99];
    return $events;
  }

}
