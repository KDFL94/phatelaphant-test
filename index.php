<?php

class Travel
{
  public function fetchTravels() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels');
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
  }
}

class Company
{
  public function fetchCompanies() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies');
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
  }
}

class TestScript
{
  private function makeRecursive($d, $r = 0, $pk = 'parentId', $k = 'id', $c = 'children') {
    $m = array();
    foreach ($d as $e) {
      isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
      isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
      $m[$e[$pk]][] = array_merge($e, array($c => &$m[$e[$k]]));
    }

    return $m[$r][0];
  }

  public function execute()
  {
    $start = microtime(true);
    $travel = new Travel();
    $travels = $travel->fetchTravels();
    $company = new Company();
    $companies = $company->fetchCompanies();
    $companyArr = [];

    // create companies array
    foreach ( $companies as $acompany ) {
      $companyArr[$acompany->id] = [
        'id' => $acompany->id,
        'name' => $acompany->name,
        'cost' => 0,
        'parentId' => $acompany->parentId,
        'children' => []
      ];
    }

    // calculate cost
    $totalCost = 0;
    foreach ( $travels as $atravel ) {
      $companyArr[$atravel->companyId]['cost'] = $atravel->price + $companyArr[$atravel->companyId]['cost'];
      $totalCost += $atravel->price;
    }

    $result = $this->makeRecursive(array_values($companyArr));

    // update total cost
    $result['cost'] = $totalCost;
    unset($result['parentId']);

    echo '<pre>';
    echo json_encode($result, JSON_PRETTY_PRINT);
    echo '</pre>';
    echo 'Total time: '.  (microtime(true) - $start);
  }
}

(new TestScript())->execute();