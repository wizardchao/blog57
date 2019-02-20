$(function () {
    // banner轮播

        var $slider = $('#m-slider');
//        var $icons = $('#icons');
        var $li = $slider.children('li');
        var WIDTH = $li.width();
//        li的数量
        var SIZE = $li.size();
        $slider.append($li.first().clone());
        //console.log(WIDTH + '-' + SIZE);
        var ox,mx,ux,sumx,scroll,i=0,bool=false,staut=true;
        $li.find('a').click(function(){
            //阻止轮播元素的默认点击事件
            return false;
        });
        $slider.mousedown(function(e){
            //鼠标左键轮播区域
            if(e.target.tagName == 'IMG' && e.button == 0){
                //左键图片
                staut = true;
                //初始化拖拽,状态为true,可以触发点击事件
                sumx = 0;
                //初始化鼠标偏移为0
                bool = true;
                //记录左键状态
                ox = e.pageX;
                //记录鼠标初始坐标
                scroll = $slider.parent().scrollLeft();
                //记录初始轮播水平滚动偏移
                e.preventDefault();
                //阻止鼠标点击默认事件
            }
        });
        $slider.mousemove(function(e){
            //鼠标在轮播区域移动
            if(bool){//左键状态
                staut = false;
                //已经拖拽,状态为false,不再触发点击事件
                mx = e.pageX;
                //记录鼠标实时坐标
                sumx = ox - mx;
                //记录鼠标坐标偏移
                $slider.parent().scrollLeft(scroll+sumx);
            }
        });
        $slider.mouseout(function(e){
            //鼠标离开轮播区域
            if(bool){
                //左键状态
                staut = true;
                //已经拖拽,但是离开了轮播区域,
                //状态为true,可以触发点击事件
                bool = false;//释放左键状态
                sumx > 0 && i < SIZE && i++;//下一个
                sumx < 0 && i > 0 && i--;//上一个
                $slider.parent().stop().animate({scrollLeft:i*WIDTH},300,function(){
                    if(i == SIZE){
                        i = 0;
                        $slider.parent().scrollLeft(0);
                    }

                });//完成拖拽
            }
        });
        $slider.mouseup(function(e){
            //鼠标释放,完成click事件
            bool = false;
            //释放左键状态
            if(staut && e.button == 0){
                //没有拖拽或者拖拽失效,且是左键,触发点击事件
                window.location.href = $(e.target).parent().attr('href');
                //触发点击事件
            }else if(!staut && e.button == 0){
                //成功拖拽,且是左键
                sumx > 0 && i < SIZE && i++;//下一个
                sumx < 0 && i > 0 && i--;//上一个
                $slider.parent().stop().animate({scrollLeft:i*WIDTH},500,function(){
                    if(i == SIZE){
                        //最后一个
                        i = 0;

                        $slider.parent().scrollLeft(0);//归位
                    }
                });//完成拖拽
            }
        });
        function setSlider(){
            i < SIZE && i++;//下一个
            $slider.parent().stop().animate({scrollLeft:i*WIDTH},500,function(){

                if(i == SIZE){//最后一个
                    i = 0;
                    $('.buttons').fadeIn(800)
                    setTimeout(function () {
                        $('.buttons').fadeOut(800)
                    },4000)
                    $slider.parent().scrollLeft(0);

                }

            });//完成拖拽
        }
        var timer = setInterval(function(){
            setSlider();
        },6000);
        $slider.hover(function(){
            if(timer){
                clearInterval(timer);
                timer = null;
            }
        },function(){
            timer = setInterval(function(){
                setSlider();
            },6000);
        });

        $(window).resize(function(){
            WIDTH = $li.width();
            $slider.parent().scrollLeft(i*WIDTH);//归位

        });

    // 活动现场轮播
    jQuery(".slideBox").slide({mainCell:".bd ul",effect:"leftLoop"});


    // 首页底部轮播
    jQuery(".picMarquee-left").slide({mainCell:".bd .item-list",autoPlay:true,effect:"leftMarquee",vis:4,interTime:20,trigger:"click"});


})

