<?php

namespace Ouhaohan8023\IDInfo\Service;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
            try {
                if ($p) {
                    Xzqh::query()->firstOrCreate([
                        'code' => $m[1][0],
                        'title' => $m[2][0],
                        'type' => 2,
                        'parent_id' => $p->id,
                        'superior_id' => $s->id,
                    ]);
                } elseif ($s) {
                    Xzqh::query()->firstOrCreate([
                        'code' => $m[1][0],
                        'title' => $m[2][0],
                        'type' => 2,
                        'parent_id' => $s->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('初始化部分失败', [$e->getMessage()]);
            }

        }
    }

    public static function info($id)
    {
        $six = substr($id, 0, 6);
        $q = Xzqh::query()->with(['parent', 'superior'])->where('code', $six)->first();

        if (!$q) {
            throw new \Exception('未找到对应的行政区划');
        }
        $m = new IDInfoModel();
        if ($q->superior) {
            $m->province_code = $q->superior->code;
            $m->province = $q->superior->title;
        } else {
            $m->province_code = $q->parent->code;
            $m->province = $q->code;
        }
        $m->city_code = $q->parent->code;
        $m->area_code = $q->code;
        $m->city = $q->parent->title;
        $m->area = $q->title;

        $m->age = self::getAge(substr($id, 6, 8));

        $m->sex = self::getSex($id);

        $m->zodiac = self::getZodiac($id);

        return $m;
    }

    private static function getAge($string)
    {
        $b = Carbon::createFromFormat('Ymd', $string);
        $now = Carbon::now();

        return $now->diffInYears($b);
    }

    private static function getSex($string)
    {
        $secondLastCharacter = substr($string, -2, 1);

        return $secondLastCharacter % 2 === 0 ? '女' : '男';
    }

    private static function getZodiac($string)
    {
        $birthYear = substr($string, 6, 4);
        $startYear = 1900;
        $zodiacs = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];

        if (is_numeric($birthYear)) {
            $index = ($birthYear - $startYear) % 12;
            return $zodiacs[$index];
        }

        return null;
    }

    /**
     * 校验18位身份证是否合法
     * @param $idcode
     * @return bool
     */
    public static function check18($idcode)
    {
        // 加权因子
        $weight_factor = [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2];
        // 校验码
        $check_code = ['1', '0', 'X' , '9', '8', '7', '6', '5', '4', '3', '2'];
        $code = $idcode . "";
        $last = $idcode[17];//最后一个
        $seventeen = substr($code, 0, 17);
        // ISO 7064:1983.MOD 11-2
        // 判断最后一位校验码是否正确
        $arr = str_split($seventeen);
        $len = count($arr);
        $num = 0;
        for($i = 0; $i < $len; $i++){
            $num = $num + $arr[$i] * $weight_factor[$i];
        }
        // 获取余数
        $resisue = $num % 11;
        $last_no = $check_code[$resisue];
        // 格式的正则
        $idcard_patter = "/^[1-9][0-9]{5}([1][9][0-9]{2}|[2][0][0|1][0-9])([0][1-9]|[1][0|1|2])([0][1-9]|[1|2][0-9]|[3][0|1])[0-9]{3}([0-9]|[X])$/";
        // 判断格式是否正确
        $format = preg_match($idcard_patter, $idcode);
        // 返回验证结果，校验码和格式同时正确才算是合法的身份证号码
        return $last === $last_no && $format ? true : false;
    }
}
