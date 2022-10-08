<?
/**
 * Звіт по неперевіреним товарам
 */
class Audit_Kpi extends ActionClass
{
  /**
   * Головний звіт
   */
  public function action_index() {

    $date_begin = substr($this->request->get->date_begin ?? date_create('first day of this month')->format('Y-m-d'), 0, 10);
    $date_begin = @date_create($date_begin);
    if($date_begin === false) {
      $data['date_begin'] = date_create('first day of this month')->format('Y-m-d');
    } else {
      $data['date_begin'] = $date_begin->format('Y-m-d');
    }
    
    $date_end = substr($this->request->get->date_end ?? date_create('last day of this month')->format('Y-m-d'), 0, 10);
    $date_end = @date_create($date_end);
    if($date_end === false) {
      $data['date_end'] = date_create('last day of this month')->format('Y-m-d');
    } else {
      $data['date_end'] = $date_end->format('Y-m-d');
    }

      
    $data['shift'] = (int)($this->request->get->shift ?? 30);
    $data['user_detail'] = (int)($this->request->get->user_detail ?? 0);
    if($data['shift'] < 1 || $data['shift'] >= 30) {
      $data['shift'] = 30;
    }
    $view = "index";

    $data['title'] = $this->lang->title;
    $data['menu_action'] = 'audit_kpi';
    $data['head'] = $this->load('index', 'index', 'head', '', $data);
    $data['foot'] = $this->load('index', 'index', 'foot');

    $data['report'] = $this->model->get_report($data['shift'], $data['date_begin'], date_create($data['date_end'].' + 1 day')->format('Y-m-d'), $data['user_detail']);

    $data['text_closed_products'] = $this->lang->text_closed_products;
    $data['text_out_products'] = $this->lang->text_out_products;
    $data['text_without_text'] = $this->lang->text_without_text;
    $data['text_without_require'] = $this->lang->text_without_require;
    $data['text_measure'] = $this->lang->text_measure;
    $data['text_measure_percent'] = $this->lang->text_measure_percent;
    $data['text_measure_require_product'] = $this->lang->text_measure_require_product;
    $data['text_measure_require_product_percent'] = $this->lang->text_measure_require_product_percent;
    $data['text_measure_require_product_deadline'] = $this->lang->text_measure_require_product_deadline;
    $data['text_measure_require_product_deadline_plus'] = $this->lang->text_measure_require_product_deadline_plus;
    $data['text_measure_require_total'] = $this->lang->text_measure_require_total;
    $data['text_measure_require'] = $this->lang->text_measure_require;
    $data['text_measure_require_percent'] = $this->lang->text_measure_require_percent;
    $data['text_measure_require_deadline'] = $this->lang->text_measure_require_deadline;
    $data['load_task'] = $this->lang->load_task;
    $data['text_shift'] = $this->lang->text_shift;
    $data['text_users'] = $this->lang->text_users;
    $data['text_class'] = $this->lang->text_class;
    $data['text_manager'] = $this->lang->text_manager;
    $data['text_total'] = $this->lang->text_total;



    $this->view->out($view, $data);
  }

  /**
   * Головний звіт (користувачі)
   */
  public function action_user_detail() {

    $date_begin = substr($this->request->get->date_begin ?? date_create('first day of this month')->format('Y-m-d'), 0, 10);
    $date_begin = @date_create($date_begin);
    if($date_begin === false) {
      $data['date_begin'] = date_create('first day of this month')->format('Y-m-d');
    } else {
      $data['date_begin'] = $date_begin->format('Y-m-d');
    }

    $date_end = substr($this->request->get->date_end ?? date_create('last day of this month')->format('Y-m-d'), 0, 10);
    $date_end = @date_create($date_end);
    if($date_end === false) {
      $data['date_end'] = date_create('last day of this month')->format('Y-m-d');
    } else {
      $data['date_end'] = $date_end->format('Y-m-d');
    }

    $data['shift'] = (int)($this->request->get->shift ?? 10);
    if($data['shift'] < 1 || $data['shift'] > 30) {
      $data['shift'] = 10;
    }
    $view = "main";

    $data['title'] = $this->lang->title_main;
    $data['menu_action'] = 'check_detail';
    $data['head'] = $this->load('index', 'index', 'head', '', $data);
    $data['foot'] = $this->load('index', 'index', 'foot');

    $data['report'] = $this->model->get_main_user_report($data['shift'], $data['date_begin'], $data['date_end']);

    $data['text_product_code'] = $this->lang->text_product_code;
    $data['text_product_name'] = $this->lang->text_product_name;
    $data['text_in_work'] = $this->lang->text_in_work;
    $data['text_complete_work'] = $this->lang->text_complete_work;
    $data['text_not_expired'] = $this->lang->text_not_expired;
    $data['text_expired'] = $this->lang->text_expired;
    $data['text_take'] = $this->lang->text_take;
    $data['text_pre'] = $this->lang->text_pre;
    $data['text_post'] = $this->lang->text_post;
    $data['text_stop'] = $this->lang->text_stop;
    $data['text_total'] = $this->lang->text_total;
    $data['text_client'] = $this->lang->text_client;
    $data['load_task'] = $this->lang->load_task;
    $data['text_shift'] = $this->lang->text_shift;
    $data['text_status'] = $this->lang->text_status;
    $data['text_total'] = $this->lang->text_total;
    $data['text_users'] = $this->lang->text_users;

    $this->view->out($view, $data);
  }

}