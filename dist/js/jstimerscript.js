$(document).ready(function()
{
    const timer = $('#timer')
    const s = $(timer).find('.seconds')
    const m = $(timer).find('.minutes')
    const h = $(timer).find('.hours')

    var interval = null;
    var clockType = 'countdown';

    $('button#stop-timer').on('click', function() {
        pauseClock()
    })

    $('button#reset-timer').on('click', function() {
        restartClock()
    })

    $('button#resume-timer').on('click', function() {
      $('button#resume-timer').fadeOut(100)
      $('button#reset-timer').fadeOut(100)
      switch (clockType) 
      {
        case 'countdown':
            countdown()
            break
        case 'cronometer':
            cronometer()
            break
        default:
            break;
      }
    })
})

const timer = $('#timer')
const s = $(timer).find('.seconds')
const m = $(timer).find('.minutes')
const h = $(timer).find('.hours')

var seconds = 0
var minutes = 0
var hours = 0

var interval = null;
var clockType = 'countdown';

function pad(d)
{
    return (d < 10) ? '0' + d.toString() : d.toString()
}

function startClock(measure,num)
{
    hasStarted = false
    hasEnded = false

    seconds = 0
    minutes = 0
    hours = 0

    switch (measure) 
    {
        case 's':
            if(num > 3599)
            {

                let hou = Math.floor(num / 3600)
                hours = hou
                let min = Math.floor((num - (hou * 3600)) / 60)
                minutes = min;
                let sec = (num - (hou * 3600)) - (min * 60)
                seconds = sec
            }
            else if(num > 59) 
            {
                let min = Math.floor(num / 60)
                minutes = min
                let sec = num - (min * 60)
                seconds = sec
            }
            else 
            {
                seconds = num
            }
            break
        case 'm':
            if(num > 59)
            {
                let hou = Math.floor(num / 60)
                hours = hou
                let min = num - (hou * 60)
                minutes = min
            }
            else
            {
                minutes = num
            }
            break
        case 'h':
            hours = num
            break
        default:
            break
    }

    $('button#resume-timer').fadeOut(100)
    if(seconds <= 10 && clockType == 'countdown' && minutes == 0 && hours == 0)
    {
      $(timer).find('span').addClass('red')
    }

    refreshClock()

    $('.input-wrapper').slideUp(350)
    setTimeout(function(){
        $('#timer').fadeIn(350)
        $('#stop-timer').fadeIn(350)

    }, 350)

   switch (clockType) 
   {
       case 'countdown':
            countdown()
            break
        case 'cronometer':
            cronometer()
            break
       default:
           break;
   }
}

function restartClock()
{
    clear(interval)
    hasStarted = false
    hasEnded = false

    seconds = 0
    minutes = 0
    hours = 0

    $(s).text('00')
    $(m).text('00')
    $(h).text('00')

    $(timer).find('span').removeClass('red')

    $('#timer').fadeOut(350)
    $('#stop-timer').fadeOut(100)
    $('button#resume-timer').fadeOut(100)
    $('button#reset-timer').fadeOut(100)
    setTimeout(function(){
        $('.input-wrapper').slideDown(350)
    },350)
}

function pauseClock()
{
  clear(interval)
  $('#resume-timer').fadeIn()
  $('#reset-timer').fadeIn()
}

var hasStarted = false
var hasEnded = false
if(hours == 0 && minutes == 0 && seconds == 0 && hasStarted == true) 
{
    hasEnded = true
}

function countdown() 
{
    hasStarted = true
    interval = setInterval(() => {
        if(hasEnded == false)
        {
            if (seconds <= 11 && minutes == 0 && hours == 0)
            {
              $(timer).find('span').addClass('red')
            }

            if(seconds == 0 && minutes == 0 || (hours > 0  && minutes == 0 && seconds == 0))
            {
                hours--
                minutes = 59
                seconds = 60
                refreshClock()
            }

            if(seconds > 0)
            {
                seconds--
                refreshClock()
            }
            else if(seconds == 0)
            {     
                minutes--
                seconds = 59
                refreshClock()
            }
        }
        else
        {
            restartClock()
        }

    }, 1000)
}

function cronometer()
{
    hasStarted = true
    interval = setInterval(() => {
        if(seconds < 59)
        {
            seconds++
            refreshClock()
        }
        else if(seconds == 59)
        {
            minutes++
            seconds = 0
            refreshClock()
        }

        if(minutes == 60)
        {
            hours++
            minutes = 0
            seconds = 0
            refreshClock()
        }

    }, 1000)
}

function refreshClock()
{
    $(s).text(pad(seconds))
    $(m).text(pad(minutes))
    if (hours < 0)
    {
        $(s).text('00')
        $(m).text('00')
        $(h).text('00')
    }
    else
    {
        $(h).text(pad(hours))
    }

    if(hours == 0 && minutes == 0 && seconds == 0 && hasStarted == true) 
    {
        var Qmacaddr = document.getElementById('Qmacaddr').value;
        hasEnded = true
        //alert('The Timer has Ended !')
        //location.reload();
        //localStorage.setItem("scrollTop", $(document).scrollTop());//document.body.scrollTop
        location.href = '../index.php?Qmacaddr='+Qmacaddr;
    }
}

function clear(intervalID)
{
    clearInterval(intervalID)
    //console.log('cleared the interval called ' + intervalID)
}
