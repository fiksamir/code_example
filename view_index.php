<?= $head ?>
<div class="row">
  <div class="col-12">
    <div class="form-check form-check-inline col-2">
      <input id="date_begin" class="form-control form-control-sm" type="date" value="<?=$date_begin?>">
    </div>
    <div class="form-check form-check-inline col-2">
      <input id="date_end" class="form-control form-control-sm" type="date" value="<?=$date_end?>">
    </div>
    <div class="form-check form-check-inline col-4">
      <label for="shift" class="col-form-label text-nowrap" style="margin-right: 10px;"><?= $text_shift ?></label>  
      <input id="shift" class="form-control form-control-sm" type="range" min="1" max="30" value="<?=$shift?>">
      <label id="shift_day" class="col-form-label" style="margin-left: 10px;"><?=$shift?></label>  
    </div>
    <div class="form-check form-check-inline col-1">
      <button id="end-reload" type="button" class="btn btn-primary btn-sm"><?=$load_task?></button>
    </div>
    <div class="form-check form-check-inline switch col-2">
      <label for="user-detail"><?=$text_users?></label>
      <i class="fa fa-fw"></i>
      <i class="fa fa-toggle-off pointer mb-1 text-primary" id="user-detail"></i>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="table-responsive">
      <small>
        <table class="table table-sm table-hover table-bordered">
          <thead class="">
            <tr>
              <?if($user_detail>0){?>
                <th class="text-center" style="width:16%"><?= $text_manager ?></th>
              <?}?>
              <th class="text-center" style="width:16%" colspan="<?=$user_detail == 0 ? '2' : '0'?>"><?= $text_class ?></th>
              <th class="text-center" style="width:6%"><?= $text_closed_products ?></th>
              <th class="text-center" style="width:6%"><?= $text_out_products ?></th>
              <th class="text-center" style="width:6%"><?= $text_without_text ?></th>
              <th class="text-center" style="width:6%"><?= $text_without_require ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_percent ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_product ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_product_percent ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_product_deadline ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_total ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_percent ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_deadline ?></th>
              <th class="text-center" style="width:6%"><?= $text_measure_require_product_deadline_plus ?></th>
            </tr>
          </thead>
          <tbody id="class-report">
          <tr>
            <td colspan="2"><?=$text_total ?></td>
            <td class="text-center"><?=($report['closed_product']==0 ? '' : $report['closed_product'])?></td>
            <td class="text-center"><?=($report['out_products']==0 ? '' : $report['out_products'])?></td>
            <td class="text-center"><?=($report['without_text']==0 ? '' : $report['without_text'])?></td>
            <td class="text-center"><?=($report['without_require']==0 ? '' : $report['without_require'])?></td>
            <td class="text-center"><?=($report['measure_sum']==0 ? '' : $report['measure_sum'])?></td>
            <td class="text-center"><?=($report['measure_percent']==0 ? '' : ($report['measure_percent']).'%')?></td>
            <td class="text-center"><?=($report['unfilled_products']==0 ? '' : $report['unfilled_products'])?></td>
            <td class="text-center"><?=($report['measure_require_product_percent']==0 ? '' : ($report['measure_require_product_percent']).'%')?></td>
            <td class="text-center"><?=($report['unfilled_products_d']==0 ? '' : $report['unfilled_products_d'])?></td>
            <td class="text-center"><?=($report['require_total']==0 ? '' : $report['require_total'])?></td>
            <td class="text-center"><?=($report['unfilled_require_total']==0 ? '' : $report['unfilled_require_total'])?></td>
            <td class="text-center"><?=($report['unfilled_require_percent']==0 ? '' : ($report['unfilled_require_percent']).'%')?></td>
            <td class="text-center"><?=($report['unfilled_require_total_d']==0 ? '' : $report['unfilled_require_total_d'])?></td>
            <td class="text-center"><?=($report['unfilled_products_d_p']==0 ? '' : $report['unfilled_products_d_p'])?></td>
          </tr>

          <?if($user_detail>0){?>
            <? foreach($report['user'] as $uid => $row) { ?>
              <tr>
                <td colspan="2"><i class="fa fa-plus-square-o pointer tree-user" data-user-id="<?=$uid?>" data-state="0"></i> <?=htmlentities($row['name']) ?></td>
                <td class="text-center"><?=($row['closed_product']==0 ? '' : $row['closed_product'])?></td>
                <td class="text-center"><?=($row['out_products']==0 ? '' : $row['out_products'])?></td>
                <td class="text-center"><?=($row['without_text']==0 ? '' : $row['without_text'])?></td>
                <td class="text-center"><?=($row['without_require']==0 ? '' : $row['without_require'])?></td>
                <td class="text-center"><?=($row['measure_sum']==0 ? '' : $row['measure_sum'])?></td>
                <td class="text-center"><?=($row['measure_percent']==0 ? '' : ($row['measure_percent']).'%')?></td>
                <td class="text-center"><?=($row['unfilled_products']==0 ? '' : $row['unfilled_products'])?></td>
                <td class="text-center"><?=($row['measure_require_product_percent']==0 ? '' : ($row['measure_require_product_percent']).'%')?></td>
                <td class="text-center"><?=($row['unfilled_products_d']==0 ? '' : $row['unfilled_products_d'])?></td>
                <td class="text-center"><?=($row['require_total']==0 ? '' : $row['require_total'])?></td>
                <td class="text-center"><?=($row['unfilled_require_total']==0 ? '' : $row['unfilled_require_total'])?></td>
                <td class="text-center"><?=($row['unfilled_require_percent']==0 ? '' : ($row['unfilled_require_percent']).'%')?></td>
                <td class="text-center"><?=($row['unfilled_require_total_d']==0 ? '' : $row['unfilled_require_total_d'])?></td>
                <td class="text-center"><?=($row['unfilled_products_d_p']==0 ? '' : $row['unfilled_products_d_p'])?></td>
              </tr>
              <? foreach($row['class'] as $cid => $r) { ?>
                <tr class="tree-state <?=$user_detail > 0 ? 'd-none' : ''?>" data-user-id="<?=$uid?>" data-class-id="0" data-user-state="0">
                  <td colspan="2">
                    <a class="col-sm-1" href="/tree/class/index/<?= $cid ?>"><button type="button" class="btn btn-sm btn-primary mb-0"><i class="fa fa-eye"></i></button></a>
                    <?=htmlentities($r['name']) ?></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=1&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['closed_product']==0 ? '' : $r['closed_product'])?></a></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=2&shift=<?=$shift?>"><?=($r['out_products']==0 ? '' : $r['out_products'])?></a></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=3&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['without_text']==0 ? '' : $r['without_text'])?></a></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=6&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['without_require']==0 ? '' : $r['without_require'])?></a></td>
                  <td class="text-center"><?=($r['measure_sum']==0 ? '' : $r['measure_sum'])?></td>
                  <td class="text-center"><?=($r['measure_percent']==0 ? '' : ($r['measure_percent']).'%')?></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=4&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['unfilled_products']==0 ? '' : $r['unfilled_products'])?></a></td>
                  <td class="text-center"><?=($r['measure_require_product_percent']==0 ? '' : ($r['measure_require_product_percent']).'%')?></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=5&shift=<?=$shift?>"><?=($r['unfilled_products_d']==0 ? '' : $r['unfilled_products_d'])?></a></td>
                  <td class="text-center"><?=($r['require_total']==0 ? '' : $r['require_total'])?></td>
                  <td class="text-center"><?=($r['unfilled_require_total']==0 ? '' : $r['unfilled_require_total'])?></td>
                  <td class="text-center"><?=($r['unfilled_require_percent']==0 ? '' : ($r['unfilled_require_percent']).'%')?></td>
                  <td class="text-center"><?=($r['unfilled_require_total_d']==0 ? '' : $r['unfilled_require_total_d'])?></td>
                  <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=7&shift=<?=$shift?>"><?=($r['unfilled_products_d_p']==0 ? '' : $r['unfilled_products_d_p'])?></a></td>
                </tr>
              <? } ?>
            <? } ?>
          <? }else{?>
            <? foreach($report['class'] as $cid => $r) { ?>
              <tr class="tree-state" data-class-id="0" data-user-state="0">
                <td colspan="2">
                  <a class="col-sm-1" href="/tree/class/index/<?= $cid ?>"><button type="button" class="btn btn-sm btn-primary mb-0"><i class="fa fa-eye"></i></button></a>
                  <?=htmlentities($r['name']) ?></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=1&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['closed_product']==0 ? '' : $r['closed_product'])?></a></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=2&shift=<?=$shift?>"><?=($r['out_products']==0 ? '' : $r['out_products'])?></a></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=3&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['without_text']==0 ? '' : $r['without_text'])?></a></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=6&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['without_require']==0 ? '' : $r['without_require'])?></a></td>
                <td class="text-center"><?=($r['measure_sum']==0 ? '' : $r['measure_sum'])?></td>
                <td class="text-center"><?=($r['measure_percent']==0 ? '' : ($r['measure_percent']).'%')?></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=4&date_begin=<?=$date_begin?>&date_end=<?=$date_end?>"><?=($r['unfilled_products']==0 ? '' : $r['unfilled_products'])?></a></td>
                <td class="text-center"><?=($r['measure_require_product_percent']==0 ? '' : ($r['measure_require_product_percent']).'%')?></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=5&shift=<?=$shift?>"><?=($r['unfilled_products_d']==0 ? '' : $r['unfilled_products_d'])?></a></td>
                <td class="text-center"><?=($r['require_total']==0 ? '' : $r['require_total'])?></td>
                <td class="text-center"><?=($r['unfilled_require_total']==0 ? '' : $r['unfilled_require_total'])?></td>
                <td class="text-center"><?=($r['unfilled_require_percent']==0 ? '' : ($r['unfilled_require_percent']).'%')?></td>
                <td class="text-center"><?=($r['unfilled_require_total_d']==0 ? '' : $r['unfilled_require_total_d'])?></td>
                <td class="text-center"><a class="text-info" href="/index/search/kpi/<?=$cid?>?q=7&shift=<?=$shift?>"><?=($r['unfilled_products_d_p']==0 ? '' : $r['unfilled_products_d_p'])?></a></td>
              </tr>
            <? } ?>
          <? } ?>
        </tbody>
        </table>
      </small>
    </div>
  </div>
</div>
<script>
  $(function () {
    if(location.href.indexOf('user_detail') !== -1){
      $('#user-detail').removeClass('fa-toggle-off').addClass('fa-toggle-on');
    }
    $('#end-reload').on('click', function() {
      let i = $('#user-detail');
      if (i.hasClass('fa-toggle-off')) {
        location.href = '/audit/kpi/?date_begin=' + $('#date_begin').val() + '&date_end=' + $('#date_end').val() + '&shift=' + $('#shift').val();
      } else if(i.hasClass('fa-toggle-on')) {
        location.href = '/audit/kpi/?date_begin=' + $('#date_begin').val() + '&date_end=' + $('#date_end').val() + '&shift=' + $('#shift').val() + '&user_detail=1';
      }
    });
    
    $('#shift').on('mousemove change', function() {
      $('#shift_day').text($(this).val());
    });
    $('i.tree-user').on('click', function() {
      var i = $(this);
      var s = i.attr('data-state');
      var uid = i.attr('data-user-id');
      if(s == 0) {
        i.addClass('fa-minus-square-o');
        i.removeClass('fa-plus-square-o');
        i.attr('data-state', '1');
        $('#class-report tr.tree-state[data-user-id="'+uid+'"]').attr('data-user-state', '1');
      } else {
        i.removeClass('fa-minus-square-o');
        i.addClass('fa-plus-square-o');
        i.attr('data-state', '0');
        $('#class-report tr.tree-state[data-user-id="'+uid+'"]').attr('data-user-state', '0');
      }
      work_state();
    });

    $('#user-detail').on('click', function () {
      let i = $(this);
      if (i.hasClass('fa-toggle-off')){
        i.removeClass('fa-toggle-off');
        i.addClass('fa-toggle-on');
      } else {
        i.removeClass('fa-toggle-on');
        i.addClass('fa-toggle-off');
      }
    });
    
    function work_state() {
      var trs = $('#class-report tr.tree-state');
      for(var i=0; i<trs.length; i++) {
        var tr = $(trs[i]);
        if(tr.attr('data-user-state') == '0' || tr.attr('data-class-state') == '0') {
          tr.addClass('d-none');
        } else if(tr.attr('data-user-state') == '1' && tr.attr('data-class-state') == '1') {
          tr.removeClass('d-none');
        } else if(tr.attr('data-user-state') == '1' && tr.attr('data-class-id') == '0') {
          tr.removeClass('d-none');
        } else {
          tr.addClass('d-none');
        }
      }
    }
  });
</script>
<?= $foot ?>