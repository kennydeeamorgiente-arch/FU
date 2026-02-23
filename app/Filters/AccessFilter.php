<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AccessFilter implements FilterInterface
{
  public function before(RequestInterface $request, $arguments = null)
  {
    $session = session();
    $access_id = $session->get('access_id');

    if (!$access_id) {
      return redirect()->to('/login'); // not logged in
    }

    if ($arguments) {
      if (!in_array($access_id, $arguments)) {
        return redirect()->to('/no-access'); // forbidden page
      }
    }
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // optional, can be empty
  }
}
?>