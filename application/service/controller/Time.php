<?php
// + 补充：返回指定时间单位的日期范围 20190824 Jerry
// + 默认返回时间戳
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 刘志淳 <chun@engineer.com>
// +----------------------------------------------------------------------
namespace app\service\controller;

class Time
{
    /**
     * 返回今日开始和结束的时间戳
     *
     * @return array
     */
    public static function todayTime()
    {
        return [
            mktime(0, 0, 0, date('m'), date('d'), date('Y')),
            mktime(23, 59, 59, date('m'), date('d'), date('Y'))
        ];
    }
    
    /**
     * 返回今日日期
     *
     * @return string
     */
    public static function todayDate(string $delimiter = '')
    {
        return date("Y{$delimiter}m{$delimiter}d");
    }

    /**
     * 返回昨日开始和结束的时间戳
     *
     * @return array
     */
    public static function yesterdayTime()
    {
        $yesterday = date('d') - 1;
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
        ];
    }

	/**
     * 返回昨日日期
     *
     * @return string
     */
    public static function yesterdayDate(string $delimiter = '')
    {
        return date("Y{$delimiter}m{$delimiter}d", strtotime("-1 day"));
    }

    /**
     * 返回本周开始和结束的时间戳
     *
     * @return array
     */
    public static function weekTime()
    {
        $timestamp = time();
        if (date('w', $timestamp) == 1) {
            $start_week = "+0 week Monday";
        } else {
            $start_week = "-1 week Monday";
        }
        
        return [
            strtotime(date('Y-m-d', strtotime($start_week, $timestamp))),
            strtotime(date('Y-m-d', strtotime("+0 week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

	/**
     * 返回本周开始和结束的日期
     *
     * @return array
     */
    public static function weekDate(string $delimiter = '')
    {
        $timestamp = time();
        if (date('w', $timestamp) == 1) {
            $start_week = "+0 week Monday";
        } else {
            $start_week = "-1 week Monday";
        }

        return [
            date("Y{$delimiter}m{$delimiter}d", strtotime($start_week, $timestamp)),
            date("Y{$delimiter}m{$delimiter}d", strtotime("+0 week Sunday", $timestamp) + 24 * 3600 - 1)
        ];
    }

    /**
     * 返回上周开始和结束的时间戳
     *
     * @return array
     */
    public static function lastWeekTime()
    {
        $timestamp = time();
        return [
            strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
            strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

	/**
     * 返回上周开始和结束的日期
     *
     * @return array
     */
    public static function lastWeekDate(string $delimiter = '')
    {
        $timestamp = time();
        return [
            date("Y{$delimiter}m{$delimiter}d", strtotime("last week Monday", $timestamp)),
            date("Y{$delimiter}m{$delimiter}d", strtotime("last week Sunday", $timestamp) + 24 * 3600 - 1)
        ];
    }

    /**
     * 返回本月开始和结束的时间戳
     *
     * @return array
     */
    public static function monthTime()
    {
        return [
            mktime(0, 0, 0, date('m'), 1, date('Y')),
            mktime(23, 59, 59, date('m'), date('t'), date('Y'))
        ];
    }

	/**
     * 返回本月开始和结束的日期
     *
     * @return array
     */
    public static function monthDate(string $delimiter = '')
    {
        return [
            date('Y').$delimiter.date('m').$delimiter.'01',
			date('Y').$delimiter.date('m').$delimiter.date('t')
        ];
    }
    
    /**
     * 返回本月
     *
     * @return array
     */
    public static function month(string $delimiter = '')
    {
          return date("Y{$delimiter}m");
    }

    /**
     * 返回上个月开始和结束的时间戳
     *
     * @return array
     */
    public static function lastMonthTime()
    {
        $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));

        return [$begin, $end];
    }

	/**
     * 返回上个月开始和结束的日期
     *
     * @return array
     */
    public static function lastMonthDate(string $delimiter = '')
    {
		$last_month = date('m')-1;
        $begin = date('Y').$delimiter.$last_month.$delimiter.'01';
        $end = date('Y').$delimiter.$last_month.$delimiter.date('t', $begin);

        return [$begin, $end];
    }

    /**
     * 返回上个月份
     * @return int
     */
    public static function lastMonth(string $delimiter = '')
    {
        return date("Y{$delimiter}m", strtotime("first day of last month"));
    }
    
    /**
     * 返回今年开始和结束的时间戳
     *
     * @return array
     */
    public static function yearTime()
    {
        return [
            mktime(0, 0, 0, 1, 1, date('Y')),
            mktime(23, 59, 59, 12, 31, date('Y'))
        ];
    }

    /**
     * 返回今年开始和结束的日期
     *
     * @return array
     */
    public static function yearDate(string $delimiter = '')
    {
        return [
            date('Y').$delimiter.'01'.$delimiter.'01',
			date('Y').$delimiter.'12'.$delimiter.'31',
        ];
    }

    /**
     * 返回去年开始和结束的时间戳
     *
     * @return array
     */
    public static function lastYearTime()
    {
        $year = date('Y') - 1;
        return [
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        ];
    }

    /**
     * 返回去年开始和结束的日期
     *
     * @return array
     */
    public static function lastYearDate(string $delimiter = '')
    {
        $year = date('Y') - 1;
        return [
            $year.$delimiter.'01'.$delimiter.'01',
			$year.$delimiter.'12'.$delimiter.'31',
        ];
    }

    public static function dayOf()
    {

    }

    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow($day = 1, $now = true)
    {
        $end = time();
        if (!$now) {
            list($foo, $end) = self::yesterday();
        }

        return [
            mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
            $end
        ];
    }

    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo($day = 1)
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter($day = 1)
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }

    /**
     * 天数转换成秒数
     *
     * @param int $day
     * @return int
     */
    public static function daysToSecond($day = 1)
    {
        return $day * 86400;
    }

    /**
     * 周数转换成秒数
     *
     * @param int $week
     * @return int
     */
    public static function weekToSecond($week = 1)
    {
        return self::daysToSecond() * 7 * $week;
    }

	/**
     * 日期转换成星期
     *
     * @param int $date
     * @return string
     */
    public static function dateToWeek(int $date)
    {	
		$weekday = ["周日","周一","周二","周三","周四","周五","周六"];
        return $weekday[date("w", strtotime($date))];
    }

    private static function startTimeToEndTime()
    {

    }
}