<?php
/**
 * Password hashing + verification with optional PEPPER.
 * Uses Argon2id if available, else falls back to BCRYPT.
 */

function env(string $key, $default = null) {
  $v = getenv($key);
  return $v !== false ? $v : $default;
}

function hash_password(string $password): string {
  $pepper = env('AUTH_PEPPER', '');
  $pwd = $pepper ? hash_hmac('sha256', $password, $pepper) : $password;

  if (defined('PASSWORD_ARGON2ID')) {
    $options = [
      'memory_cost' => 1<<17, // 128MB
      'time_cost'   => 3,
      'threads'     => 2,
    ];
    return password_hash($pwd, PASSWORD_ARGON2ID, $options);
  } else {
    $options = ['cost' => 12];
    return password_hash($pwd, PASSWORD_BCRYPT, $options);
  }
}

function verify_password(string $password, string $hash): bool {
  $pepper = env('AUTH_PEPPER', '');
  $pwd = $pepper ? hash_hmac('sha256', $password, $pepper) : $password;
  return password_verify($pwd, $hash);
}
