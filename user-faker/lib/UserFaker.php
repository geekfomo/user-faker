<?php
namespace gfoUserFaker;

class UserFaker {
  /** Register Hooks */
  public function register() {
    $this->setFakeUser();
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
  /** @var int The user ID of the real user - The one who was login to system */
  private int $realId = 0;
  /** @var int The user ID of the fake user - The one who was selected by the real user to pretend to */
  private int $fakeId = 0;
  /** @var string The name of the cookie we store the fake user ID */
  private string $cookieFakeId = 'wordpress_userFakeId';
  /** @var string The name of the cookie we store the fake user ID */
  private string $capFakeUser  = 'fake_user';
}
