<?
class Audit_Kpi_Model {

  public function get_report($shift, $begin, $end, $user_detail) {
    $lang = $this->lang;
    $sql = '
      WITH
        closed_products AS (
          SELECT DISTINCT p.product_id, p.class_id, p.without_require
					FROM class_product p 
					INNER JOIN class_task t ON p.product_id=t.product_id AND t.status = 2 AND date(t.finish AT TIME ZONE \'UTC+2\') >= \''.$begin.'\' AND date(t.finish AT TIME ZONE \'UTC+2\') < \''.$end.'\' AND t.user_id <> 2 AND t.type<>5 
					WHERE  
           p.isarchive = 0 AND p.has_content_user_date IS NOT NULL AND
           date(p.has_content_user_date AT TIME ZONE \'UTC+2\') >= \''.$begin.'\' AND
           date(p.has_content_user_date  AT TIME ZONE \'UTC+2\') < \''.$end.'\'     
        ),
        closed_sum AS (
          SELECT count(product_id) as closed_product, class_id
          FROM closed_products 
          GROUP BY class_id
        ),       
        without_text AS (
          SELECT count(DISTINCT p.product_id) as without_text, p.class_id
          FROM class_task s
          INNER JOIN closed_products cp ON cp.product_id = s.product_id
          INNER JOIN class_product p ON cp.product_id=p.product_id AND (length(desc_ua)=0 OR desc_ua is NULL OR length(desc_ru)=0 OR desc_ru is NULL) AND p.isarchive = 0          
          WHERE s.view = 1 AND date(s.finish AT TIME ZONE \'UTC+2\') >= \''.$begin.'\' AND date(s.finish AT TIME ZONE \'UTC+2\') < \''.$end.'\'         
          GROUP BY p.class_id
        ),
        without_require AS (
          SELECT count(p.product_id) as without_require, p.class_id
					FROM closed_products p 			  
					WHERE  
						p.without_require = true
					GROUP BY class_id
        ),
        measure_all AS (
          SELECT DISTINCT cm.measure_id, p.class_id
          FROM class_task t 
          INNER JOIN class_product p ON t.product_id=p.product_id AND p.has_content_user_date IS NOT NULL AND p.isarchive = 0 AND p.checked=true 
          INNER JOIN class_class_measure cm ON p.class_id=cm.class_id AND cm."enable" = true
          INNER JOIN class_measure m ON m.measure_id=cm.measure_id AND m.discount = false AND m."enable" = true
          LEFT JOIN class_task s ON p.product_id=s.product_id AND s.type = 5 AND date(s.finish AT TIME ZONE \'UTC+2\') >= \''.$begin.'\' AND date(s.finish AT TIME ZONE \'UTC+2\') < \''.$end.'\'    
          WHERE date(t.finish AT TIME ZONE \'UTC+2\') >= \''.$begin.'\' AND date(t.finish AT TIME ZONE \'UTC+2\') < \''.$end.'\' AND s.product_id IS NULL       
			  ), 
        measure_sum AS (
          SELECT (count(DISTINCT m.measure_id)*cs.closed_product) AS measure_sum, m.class_id
          FROM measure_all m
          INNER JOIN closed_sum cs ON cs.class_id = m.class_id
					GROUP BY m.class_id, cs.closed_product
        ),
        filled_measure AS (
          SELECT DISTINCT o.measure_id, cp.class_id, cp.product_id
          FROM closed_products cp
          INNER JOIN class_product_option po ON po.product_id = cp.product_id
          LEFT JOIN class_option o ON o.option_id = po.option_id AND o.enable    
          INNER JOIN class_class_measure cm ON o.measure_id=cm.measure_id AND cm."enable" = true 					      
          WHERE o.option_id is not NULL
          GROUP BY cp.class_id, cp.product_id, o.measure_id
        ),  
        filled_measure_sum AS (
          SELECT count(f.measure_id) as filled_measure_sum, f.class_id
          FROM filled_measure f
					GROUP BY f.class_id
        ),	  
        measure_percent AS (
          SELECT round(f.filled_measure_sum::numeric*100/s.measure_sum::numeric ,2) as measure_percent, f.class_id
          FROM filled_measure_sum f
          INNER JOIN measure_sum s ON s.class_id=f.class_id
          GROUP BY f.class_id, f.filled_measure_sum, s.measure_sum 
        ),
        require_measure as (
          SELECT cp.product_id, cm.measure_id, cp.class_id
          FROM closed_products cp
          INNER JOIN class_class_measure cm ON cm.class_id = cp.class_id AND cm.require AND cm.enable
        ),
        measure_product AS (
          SELECT r.product_id, count(r.measure_id) as req_measure, SUM(CASE WHEN u.measure_id is not null THEN 1 ELSE 0 END) as filled_req_measure, r.class_id
          FROM require_measure r
          LEFT JOIN filled_measure u ON u.product_id=r.product_id AND u.measure_id=r.measure_id
          GROUP BY r.class_id, r.product_id
        ),
        unfilled_products as (
          SELECT count(product_id) as unfilled_products, class_id
          FROM measure_product 
          WHERE filled_req_measure < req_measure 
          GROUP BY class_id
        ),
        total_products_with_req as (
          SELECT count(product_id) as total_products, class_id
          FROM measure_product 
          WHERE req_measure > 0 
          GROUP BY class_id
        ),
        measure_require_product_percent AS (
          SELECT round(SUM(u.unfilled_products)/t.total_products*100,2) as measure_require_product_percent, u.class_id
          FROM total_products_with_req t
          INNER JOIN unfilled_products u ON t.class_id = u. class_id
          GROUP BY u.class_id, u.unfilled_products, t.total_products
        ),     
        require_measure_deadline as (
          SELECT p.product_id, cm.measure_id, p.class_id
          FROM class_product p
          INNER JOIN class_class_measure cm ON cm.class_id = p.class_id AND cm.require AND cm.enable
          WHERE p."create" < (CURRENT_TIMESTAMP - INTERVAL \''.$shift.' days\') AND
           p.has_content_user_date IS NOT NULL AND p.isarchive = 0 AND p.checked=true AND p.without_require = false
        ),
        filled_measure_deadline AS (
          SELECT DISTINCT p.product_id, o.measure_id, p.class_id
          FROM class_product p
          INNER JOIN class_product_option po ON po.product_id=p.product_id
          INNER JOIN class_option o ON o.option_id=po.option_id AND o.enable
          WHERE p."create" < (CURRENT_TIMESTAMP - INTERVAL \''.$shift.' days\') AND
            p.has_content_user_date IS NOT NULL AND p.isarchive = 0 AND p.checked=true 
        ),
        measure_product_deadline AS (
          SELECT r.product_id, count(r.measure_id) as req_measure, SUM(CASE WHEN u.measure_id is not null THEN 1 ELSE 0 END) as filled_req_measure, r.class_id
          FROM require_measure_deadline r
          LEFT JOIN filled_measure_deadline u ON u.product_id=r.product_id AND u.measure_id=r.measure_id
          GROUP BY r.class_id, r.product_id
        ),
        unfilled_products_deadline AS (
          SELECT count(product_id) as unfilled_products_d, class_id
          FROM measure_product_deadline 
          WHERE filled_req_measure < req_measure 
          GROUP BY class_id
        ),
        unfilled_products_deadline_plus AS (
          SELECT count(DISTINCT d.product_id) as unfilled_products_d_p, d.class_id
          FROM measure_product_deadline d
          INNER JOIN class_task t ON t.product_id=d.product_id AND t.status = 2 AND t.view = 4 AND (t.finish <  CURRENT_TIMESTAMP - INTERVAL \'30 days\')
          WHERE filled_req_measure < req_measure 
          GROUP BY class_id
        ),
        count_require_measures AS (
          SELECT SUM(req_measure) as require_total, SUM(req_measure - filled_req_measure) as unfilled_require_total,
           round(SUM(req_measure - filled_req_measure)/SUM(req_measure)*100, 2) as unfilled_require_percent, class_id
          FROM measure_product
          GROUP BY class_id
        ),
        count_require_measures_deadline AS (
          SELECT SUM(req_measure) as require_total_d, SUM(req_measure - filled_req_measure) as unfilled_require_total_d,
           round(SUM(req_measure - filled_req_measure)/SUM(req_measure)*100, 2) as unfilled_require_percent_d, class_id
          FROM measure_product_deadline
          GROUP BY class_id
        )
        SELECT COALESCE(u.user_id, 0) as user_id, COALESCE(u.name, \'empty\') as user, c.name_'.$lang.' as class, c.class_id,
         closed_product, without_text, without_require, measure_sum, COALESCE(measure_percent, 0) as measure_percent, unfilled_products, 
         COALESCE(measure_require_product_percent, 0) as measure_require_product_percent, unfilled_products_d, unfilled_products_d_p, require_total, unfilled_require_total,
         COALESCE(unfilled_require_percent,0) as unfilled_require_percent, unfilled_require_total_d       
        FROM closed_sum cs
          LEFT JOIN without_text wt ON wt.class_id=cs.class_id
          LEFT JOIN without_require wr ON wr.class_id=cs.class_id
          LEFT JOIN measure_sum ms ON ms.class_id=cs.class_id
          LEFT JOIN measure_percent mp ON mp.class_id=cs.class_id
          LEFT JOIN unfilled_products up ON up.class_id=cs.class_id
          LEFT JOIN measure_require_product_percent mr ON mr.class_id=cs.class_id
          LEFT JOIN unfilled_products_deadline ud ON ud.class_id=cs.class_id
          LEFT JOIN unfilled_products_deadline_plus udp ON udp.class_id=cs.class_id
          LEFT JOIN count_require_measures cr ON cr.class_id=cs.class_id
          LEFT JOIN count_require_measures_deadline cd ON cd.class_id=cs.class_id
          LEFT JOIN class_class c ON c.class_id=cs.class_id AND c.hidden = false
          LEFT JOIN system_user u ON u.user_id=c.manager_user_id
        ORDER BY closed_product DESC 
    ';
//    echo '<pre>';
//    print_r($sql);exit;
    $res = $this->db->query($sql);

    $sql = '
      WITH
        out_products AS (
          SELECT count(DISTINCT p.product_id) as out_products, p.class_id
          FROM class_task t
          INNER JOIN class_product p ON p.product_id=t.product_id AND p.without_content=false AND p."create" < (CURRENT_TIMESTAMP - INTERVAL \''.$shift.' days\') 
          WHERE
           t.status IN (0,1,3) AND t.type<>5   
					GROUP BY p.class_id 
        )     
        SELECT COALESCE(u.user_id, 0) as user_id, COALESCE(u.name, \'empty\') as user, c.name_'.$lang.' as class, c.class_id,
         COALESCE(op.out_products, 0) as out_products       
        FROM class_class  c 
          LEFT JOIN out_products op  ON c.class_id=op.class_id 
          LEFT JOIN system_user u ON u.user_id=c.manager_user_id
        WHERE COALESCE(op.out_products, 0) <> 0 AND c.hidden = false
      ';
    $out_products = array_column($this->db->query($sql),null,'class_id');

    if($user_detail>0) {
      $arr = [
        'user' => [],
        'closed_product' => 0,
        'out_products' => 0,
        'without_text' => 0,
        'without_require' => 0,
        'measure_sum' => 0,
        'measure_percent' => 0,
        'unfilled_products' => 0,
        'measure_require_product_percent' => 0,
        'unfilled_products_d' => 0,
        'unfilled_products_d_p' => 0,
        'require_total' => 0,
        'unfilled_require_total' => 0,
        'unfilled_require_percent' => 0,
        'unfilled_require_total_d' => 0,

      ];
      foreach ($res as $row) {
        $uid = (int)$row['user_id'];
        if (!isset($arr['user'][$uid])) {
          $arr['user'][$uid] = [
            'name' => $row['user'],
            'class' => [],
            'closed_product' => 0,
            'out_products' => 0,
            'without_text' => 0,
            'without_require' => 0,
            'measure_sum' => 0,
            'measure_percent' => 0,
            'unfilled_products' => 0,
            'measure_require_product_percent' => 0,
            'unfilled_products_d' => 0,
            'unfilled_products_d_p' => 0,
            'require_total' => 0,
            'unfilled_require_total' => 0,
            'unfilled_require_percent' => 0,
            'unfilled_require_total_d' => 0,
          ];
        }
        $cid = (int)$row['class_id'];
        if (!isset($arr['user'][$uid]['class'][$cid])) {
          $arr['user'][$uid]['class'][$cid] = [
            'name' => $row['class'],
            'closed_product' => $row['closed_product'],
            'out_products' => $out_products[$cid]['out_products'] ?? 0,
            'without_text' => $row['without_text'],
            'without_require' => $row['without_require'],
            'measure_sum' => $row['measure_sum'],
            'measure_percent' => $row['measure_percent'],
            'unfilled_products' => $row['unfilled_products'],
            'measure_require_product_percent' => $row['measure_require_product_percent'],
            'unfilled_products_d' => $row['unfilled_products_d'],
            'unfilled_products_d_p' => $row['unfilled_products_d_p'],
            'require_total' => $row['require_total'],
            'unfilled_require_total' => $row['unfilled_require_total'],
            'unfilled_require_percent' => $row['unfilled_require_percent'],
            'unfilled_require_total_d' => $row['unfilled_require_total_d'],
          ];
        }

        $arr['user'][$uid]['closed_product'] += (int)$row['closed_product'];
        $arr['user'][$uid]['without_text'] += (int)$row['without_text'];
        $arr['user'][$uid]['without_require'] += (int)$row['without_require'];
        $arr['user'][$uid]['measure_sum'] += (int)$row['measure_sum'];
        $arr['user'][$uid]['measure_percent'] += (float)$row['measure_percent'];
        $arr['user'][$uid]['unfilled_products'] += (int)$row['unfilled_products'];
        $arr['user'][$uid]['measure_require_product_percent'] += (float)$row['measure_require_product_percent'];
        $arr['user'][$uid]['unfilled_products_d'] += (int)$row['unfilled_products_d'];
        $arr['user'][$uid]['unfilled_products_d_p'] += (int)$row['unfilled_products_d_p'];
        $arr['user'][$uid]['require_total'] += (int)$row['require_total'];
        $arr['user'][$uid]['unfilled_require_total'] += (int)$row['unfilled_require_total'];
        $arr['user'][$uid]['unfilled_require_percent'] += (float)$row['unfilled_require_percent'];
        $arr['user'][$uid]['unfilled_require_total_d'] += (int)$row['unfilled_require_total_d'];

        $arr['closed_product'] += (int)$row['closed_product'];
        $arr['without_text'] += (int)$row['without_text'];
        $arr['without_require'] += (int)$row['without_require'];
        $arr['measure_sum'] += (int)$row['measure_sum'];
        $arr['unfilled_products'] += (int)$row['unfilled_products'];
        $arr['unfilled_products_d'] += (int)$row['unfilled_products_d'];
        $arr['unfilled_products_d_p'] += (int)$row['unfilled_products_d_p'];
        $arr['require_total'] += (int)$row['require_total'];
        $arr['unfilled_require_total'] += (int)$row['unfilled_require_total'];
        $arr['unfilled_require_total_d'] += (int)$row['unfilled_require_total_d'];
      }
      foreach ($out_products as $o){
        $uid = (int)$o['user_id'];
        if (!isset($arr['user'][$uid])) {
          $arr['user'][$uid] = [
            'name' => $o['user'],
            'class' => [],
            'closed_product' => 0,
            'out_products' => 0,
            'without_text' => 0,
            'without_require' => 0,
            'measure_sum' => 0,
            'measure_percent' => 0,
            'unfilled_products' => 0,
            'measure_require_product_percent' => 0,
            'unfilled_products_d' => 0,
            'unfilled_products_d_p' => 0,
            'require_total' => 0,
            'unfilled_require_total' => 0,
            'unfilled_require_percent' => 0,
            'unfilled_require_total_d' => 0,
          ];
        }
        $cid = (int)$o['class_id'];
        if (!isset($arr['user'][$uid]['class'][$cid])) {
          $arr['user'][$uid]['class'][$cid] = [
            'name' => $o['class'],
            'closed_product' => 0,
            'out_products' => $o['out_products'],
            'without_text' => 0,
            'without_require' => 0,
            'measure_sum' => 0,
            'measure_percent' => 0,
            'unfilled_products' => 0,
            'measure_require_product_percent' => 0,
            'unfilled_products_d' => 0,
            'unfilled_products_d_p' => 0,
            'require_total' => 0,
            'unfilled_require_total' => 0,
            'unfilled_require_percent' => 0,
            'unfilled_require_total_d' => 0,
          ];
        }
        $arr['user'][$uid]['out_products'] += (int)($out_products[$cid]['out_products'] ?? 0);
        $arr['out_products'] += (int)($out_products[$cid]['out_products'] ?? 0);
      }
      foreach ($arr['user'] as $uid => $a) {
        $arr['user'][$uid]['measure_percent'] = round($a['measure_percent'] / count($a['class']), 2);
        $arr['user'][$uid]['measure_require_product_percent'] = round($a['measure_require_product_percent'] / count($a['class']), 2);
        $arr['user'][$uid]['unfilled_require_percent'] = round($a['unfilled_require_percent'] / count($a['class']), 2);
        $arr['measure_percent'] += (float)$arr['user'][$uid]['measure_percent'];
        $arr['measure_require_product_percent'] += (float)$arr['user'][$uid]['measure_require_product_percent'];
        $arr['unfilled_require_percent'] += (float)$arr['user'][$uid]['unfilled_require_percent'];
      }
      if (count($arr['user']) > 0) {
        $arr['measure_percent'] = round($arr['measure_percent'] / count($arr['user']), 2);
        $arr['measure_require_product_percent'] = round($arr['measure_require_product_percent'] / count($arr['user']), 2);
        $arr['unfilled_require_percent'] = round($arr['unfilled_require_percent'] / count($arr['user']), 2);
        //Сортировка по количеству закрытых товаров
        foreach($arr as &$a) {
          if(is_array($a)) {
            uasort($a, function ($b, $c) {
              if ($b['closed_product'] > $c['closed_product']) {
                return -1;
              }
              if ($b['closed_product'] == $c['closed_product']) {
                return 0;
              }
              if ($b['closed_product'] < $c['closed_product']) {
                return 1;
              }
              return 0;
            });
          }
        }
      }
    } else {
      $arr = [
        'class' => [],
        'closed_product' => 0,
        'out_products' => 0,
        'without_text' => 0,
        'without_require' => 0,
        'measure_sum' => 0,
        'measure_percent' => 0,
        'unfilled_products' => 0,
        'measure_require_product_percent' => 0,
        'unfilled_products_d' => 0,
        'unfilled_products_d_p' => 0,
        'require_total' => 0,
        'unfilled_require_total' => 0,
        'unfilled_require_percent' => 0,
        'unfilled_require_total_d' => 0,

      ];
      foreach ($res as $row) {
        $cid = (int)$row['class_id'];
        if (!isset($arr['class'][$cid])) {
          $arr['class'][$cid] = [
            'name' => $row['class'],
            'closed_product' => $row['closed_product'],
            'out_products' => $out_products[$cid]['out_products'] ?? 0,
            'without_text' => $row['without_text'],
            'without_require' => $row['without_require'],
            'measure_sum' => $row['measure_sum'],
            'measure_percent' => $row['measure_percent'],
            'unfilled_products' => $row['unfilled_products'],
            'measure_require_product_percent' => $row['measure_require_product_percent'],
            'unfilled_products_d' => $row['unfilled_products_d'],
            'unfilled_products_d_p' => $row['unfilled_products_d_p'],
            'require_total' => $row['require_total'],
            'unfilled_require_total' => $row['unfilled_require_total'],
            'unfilled_require_percent' => $row['unfilled_require_percent'],
            'unfilled_require_total_d' => $row['unfilled_require_total_d'],
          ];
        }

        $arr['closed_product'] += (int)$row['closed_product'];
        $arr['out_products'] += (int)($out_products[$cid]['out_products'] ?? 0);
        $arr['without_text'] += (int)$row['without_text'];
        $arr['without_require'] += (int)$row['without_require'];
        $arr['measure_sum'] += (int)$row['measure_sum'];
        $arr['unfilled_products'] += (int)$row['unfilled_products'];
        $arr['unfilled_products_d'] += (int)$row['unfilled_products_d'];
        $arr['unfilled_products_d_p'] += (int)$row['unfilled_products_d_p'];
        $arr['require_total'] += (int)$row['require_total'];
        $arr['unfilled_require_total'] += (int)$row['unfilled_require_total'];
        $arr['unfilled_require_total_d'] += (int)$row['unfilled_require_total_d'];
        $arr['measure_percent'] += $arr['class'][$cid]['measure_percent'];
        $arr['measure_require_product_percent'] += $arr['class'][$cid]['measure_require_product_percent'];
        $arr['unfilled_require_percent'] += $arr['class'][$cid]['unfilled_require_percent'];
      }
      foreach ($out_products as $o){
        $cid = (int)$o['class_id'];
        if (!isset($arr['class'][$cid])) {
          $arr['class'][$cid] = [
            'name' => $o['class'],
            'closed_product' => 0,
            'out_products' => $o['out_products'],
            'without_text' => 0,
            'without_require' => 0,
            'measure_sum' => 0,
            'measure_percent' => 0,
            'unfilled_products' => 0,
            'measure_require_product_percent' => 0,
            'unfilled_products_d' => 0,
            'unfilled_products_d_p' => 0,
            'require_total' => 0,
            'unfilled_require_total' => 0,
            'unfilled_require_percent' => 0,
            'unfilled_require_total_d' => 0
          ];
          $arr['out_products'] += (int)($out_products[$cid]['out_products'] ?? 0);
        }
      }

      if (count($arr['class']) > 0) {
        $arr['measure_percent'] = round($arr['measure_percent'] / count($arr['class']), 2);
        $arr['measure_require_product_percent'] = round($arr['measure_require_product_percent'] / count($arr['class']), 2);
        $arr['unfilled_require_percent'] = round($arr['unfilled_require_percent'] / count($arr['class']), 2);
      }
    }
    return $arr;
  }

}