<?php
namespace gfoUserFaker;

class UserFaker {
  /** Register Hooks */
  public function register() {
    $this->setFakeUser();
    $this->addRoutes();
    $this->addCaps();
  }
  /** Pretend to be the fake user only if user has the capability and our cookie exists and valid   */
  private function setFakeUser(): void {
    add_filter('determine_current_user', function ($userId) {
      $this->realId = $userId ?: 0;
      if ( $this->canFakeUser($this->realId) ) {
        $fakeId = $this->fetchFakeId();
        if ($fakeId > 0) {
          $this->fakeId = $fakeId;
          return $this->fakeId;
        }
      }
      return $userId;
    },50);
  }
  /** Add plugin api routes */
  private function addRoutes() : void {
    add_action('rest_api_init', function () {
      register_rest_route('user-faker', "/users", [
        'methods' => 'GET',
        'callback' => function (\WP_REST_Request $request) {
          return [
            'code'  => 0,
            'users' => $this->findValidUsers()
          ];
        },
        'permission_callback' => function () {
          return $this->canFakeUser($this->realId);
        }
      ]);
    });
  }
  /** @return array users as array with id and login keys */
  private function findValidUsers(): array {
    $query = new \WP_User_Query([
      'order' => 'ASC',
      'orderby' => 'user_login',
    ]);
    return array_map(function (\WP_User $user) {
      return ['id' => $user->ID, 'login' => $user->user_login];
    }, $query->get_results());
  }
  /**
   * @param int $userId The user to check
   * @return bool whether the user can fake user
   */
  private function canFakeUser(int $userId) : bool {
    return $userId > 0 && user_can( $this->realId, $this->capFakeUser );
  }
  /** @return int fetch the user id from our cookie if exist */
  private function fetchFakeId(): int {
    return intval($_COOKIE[$this->cookieFakeId]);
  }

  private function addCaps() : void {
    $role = get_role('administrator');
    $role->add_cap($this->capFakeUser);
  }

  /** @var int The user ID of the real user - The one who was login to system */
  private int $realId = 0;
  /** @var int The user ID of the fake user - The one who was selected by the real user to pretend to */
  private int $fakeId = 0;
  /** @var string The name of the cookie we store the fake user ID */
  private string $cookieFakeId = 'wordpress_userFakeId';
  /** @var string The name of the cookie we store the fake user ID */
  private string $capFakeUser  = 'fake_user';
}
