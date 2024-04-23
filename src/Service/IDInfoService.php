<?php

namespace Ouhaohan8023\IDInfo\Service;

use Carbon\Carbon;
use Ouhaohan8023\IDInfo\Model\IDInfoModel;
use Ouhaohan8023\IDInfo\Model\Xzqh;

class IDInfoService
{
    public static function init()
    {
        $file = __DIR__.'/../Data/2022xzqh';
        $data = file_get_contents($file);
        self::getProvince($data);
        self::getCity($data);
        self::getArea($data);
        echo 'ok';
    }

    private static function getProvince($data)
    {
        $pattern = '/\d{6}\t[\x{4e00}-\x{9fa5}]+\n/u';
        preg_match_all($pattern, $data, $matches);
        foreach ($matches[0] as $v) {
            $pattern = '/(\d{6})\t(.*?)\n/';
            preg_match_all($pattern, $v, $m);
            Xzqh::query()->firstOrCreate([
                'code' => $m[1][0],
                'title' => $m[2][0],
                'type' => 0,
            ]);
        }
    }

    private static function getCity($data)
    {
        $pattern = '/\d{6}\t [\x{4e00}-\x{9fa5}]+\n/u';
        preg_match_all($pattern, $data, $matches);
        foreach ($matches[0] as $v) {
            $pattern = '/(\d{6})\t (.*?)\n/';
            preg_match_all($pattern, $v, $m);
            $code = $m[1][0];
            $firstTwoDigits = substr($code, 0, 2);
            $p = Xzqh::query()->where('code', $firstTwoDigits.'0000')->first();
            if ($p) {
                Xzqh::query()->firstOrCreate([
                    'code' => $m[1][0],
                    'title' => $m[2][0],
                    'type' => 1,
                    'parent_id' => $p->id,
                ]);
            }
        }
    }

    private static function getArea($data)
    {
        $pattern = '/\d{6}\t   [\x{4e00}-\x{9fa5}]+\n/u';
        preg_match_all($pattern, $data, $matches);
        foreach ($matches[0] as $v) {
            $pattern = '/(\d{6})\t   (.*?)\n/';
            preg_match_all($pattern, $v, $m);
            $code = $m[1][0];
            $firstTwoDigits = substr($code, 0, 2);
            $firstFourDigits = substr($code, 0, 4);
            $p = Xzqh::query()->where('code', $firstFourDigits.'00')->first();
            $s = Xzqh::query()->where('code', $firstTwoDigits.'0000')->first();
            if ($p) {
                Xzqh::query()->firstOrCreate([
                    'code' => $m[1][0],
                    'title' => $m[2][0],
                    'type' => 2,
                    'parent_id' => $p->id,
                    'superior_id' => $s->id,
                ]);
            }
        }
    }

    public static function info($id)
    {
        $six = substr($id, 0, 6);
        $q = Xzqh::query()->with(['parent', 'superior'])->where('code', $six)->first();

        $m = new IDInfoModel();
        $m->province_code = $q->superior->code;
        $m->city_code = $q->parent->code;
        $m->area_code = $q->code;
        $m->province = $q->superior->title;
        $m->city = $q->parent->title;
        $m->area = $q->title;

        $m->age = self::getAge(substr($id, 6, 8));

        return $m;
    }

    private static function getAge($string)
    {
        $b = Carbon::createFromFormat('Ymd', $string);
        $now = Carbon::now();

        return $now->diffInYears($b);
    }
}