<footer id="footer">
  Copyright FitnessRecord.All Rights Reserved.
</footer>

<script src="js/jquery-3.4.1.min.js"></script>
<script>
  $(function(){
    
    //フッターを最下部に固定
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    }
    //メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow')
      setTimeout(function(){$jsShowMsg.slideToggle('slow')}, 5000);
    }
    // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      var file = this.files[0],            // 2. files配列にファイルが入っています
          $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
          fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト
      
      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };
      
      // 6. 画像読み込み
      fileReader.readAsDataURL(file);
      
    });
    //セット追加
    var minCount2 = 2;
    var maxCount2 = 40;//20セットまで追加可能　追加可能数を変更したい場合は数字を変更
    $('#add-set .btn').on('click', function(){
      var $addSet = $(this).parents('.form-fieldset');
      var $inputCount = $addSet.find('.weight-rep .input-traning').length;//input-traningの要素数を変数に格納
      if ($inputCount < maxCount2){
        var $element2 = $addSet.find('.weight-rep .input-traning:last-child').clone(true);//最後の要素をコピーして変数に格納
        $addSet.find('#rep-form').parent().append($element2[0]);//フォーム追加(セット)
        $addSet.find('#weight-form').parent().append($element2[1]);//フォーム追加(重量)
      }
    });
    $('#delete-set .btn').on('click', function(){
      var $deleteSet = $(this).parents('.form-fieldset');
      var $inputCount = $deleteSet.find('.weight-rep .input-traning').length;//input-traningの要素数を変数に格納
      if($inputCount > minCount2){
        $deleteSet.find('#rep-form:last-child').remove();//フォーム削除(セット)
        $deleteSet.find('#weight-form:last-child').remove();//フォーム削除(重量)
      }
    });
    //selectのvalueをinputにコピー
    $('.input-traning select').on('change', function(){
      var $selectVal = $(this).val();
      $(this).siblings('input').val($selectVal);
    });
    //トレーニング記録を削除する際の確認
    $('.delete').on('click', function(){
      if(window.confirm('このトレーニングを履歴から削除しますか？')){
        location.href = $(this).attr('href');
      }else{
        return false;
      }
    });
    //モバイルメニュー
    $('#mobile-menu').on('click', function(){
      $(this).toggleClass('active');
      $('#sidebar').toggleClass('active');
    });
  });
</script>

</body>
</html>