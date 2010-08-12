<?php

namespace fajr\libfajr\connection;
use fajr\libfajr\Trace;

class HttpToSimpleConnectionAdapter implements SimpleConnection {
  private $connection;

  public function __construct(HttpConnection $connection) {
    $this->connection = $connection;
  }

  public function request(Trace $trace, $url, $post_data = null) {
    //TODO: assert !=null && !is_array()
    if (is_array($post_data)) {
      return $this->connection->post($trace, $url, $post_data);
    }
    else {
      return $this->connection->get($trace, $url);
    }
  }

}
