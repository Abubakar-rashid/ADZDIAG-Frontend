<?php
// get_code.php
if (!isset($_POST['serial'])) {
    echo "No serial provided";
    exit;
}

$serial = strtoupper(trim($_POST['serial']));

// --- Algorithm Implementation ---
function generateSerial($serial) {
    $lookup = [
        [9,5,3,4,8,7,2,6,1,0],
        [2,1,5,6,9,3,7,0,4,8],
        [0,4,7,3,1,9,6,5,8,2],
        [5,6,4,1,2,8,0,9,3,7],
        [6,3,1,2,0,5,4,8,7,9],
        [4,0,8,7,6,1,9,3,2,5],
        [7,8,0,5,3,2,1,4,9,6],
        [1,9,6,8,7,4,5,2,0,3],
        [3,2,9,0,4,6,8,7,5,1],
        [8,7,2,9,5,0,3,1,6,4],
    ];

    $n = array_reverse(array_map('intval', str_split(substr($serial, 1, 6))));

    $n1=$n[0]; $n2=$n[1]; $n3=$n[2]; $n4=$n[3]; $n5=$n[4]; $n6=$n[5]; $n7=0;

    $r1=$lookup[$n1][5];
    $r2=$lookup[$n2][3];
    $r3=$lookup[$n3][8];
    $r4=$lookup[$n4][2];
    $r5=$lookup[$n5][1];
    $r6=$lookup[$n6][6];
    $r7=$lookup[$n7][9];

    $res1=((($lookup[$r2][$r1]+1)*($lookup[$r6][$r2]+1))+(($lookup[$r4][$r3]+1)*($lookup[$r7][$r5]+1))+($lookup[$r1][$r4]))%10;
    $res2=((($lookup[$r2][$r1]+1)*($lookup[$r5][$r4]+1))+(($lookup[$r5][$r2]+1)*($lookup[$r7][$r3]+1))+($lookup[$r1][$r6]))%10;
    $res3=((($lookup[$r2][$r1]+1)*($lookup[$r4][$r2]+1))+(($lookup[$r3][$r6]+1)*($lookup[$r7][$r4]+1))+($lookup[$r1][$r5]))%10;
    $res4=((($lookup[$r2][$r1]+1)*($lookup[$r6][$r3]+1))+(($lookup[$r3][$r7]+1)*($lookup[$r2][$r5]+1))+($lookup[$r4][$r1]))%10;

    $xres1=(($lookup[$res1][5]+1)*($lookup[$res2][1]+1))+105;
    $xres2=(($lookup[$res2][1]+1)*($lookup[$res4][0]+1))+102;
    $xres3=(($lookup[$res1][5]+1)*($lookup[$res3][8]+1))+103;
    $xres4=(($lookup[$res3][8]+1)*($lookup[$res4][0]+1))+108;

    $xres11=intdiv($xres1,10)%10; $xres10=$xres1%10;
    $xres21=intdiv($xres2,10)%10; $xres20=$xres2%10;
    $xres31=intdiv($xres3,10)%10; $xres30=$xres3%10;
    $xres41=intdiv($xres4,10)%10; $xres40=$xres4%10;

    $code3=($xres11+$xres10+$r1)%10;
    $code2=($xres21+$xres20+$r1)%10;
    $code1=($xres31+$xres30+$r1)%10;
    $code0=($xres41+$xres40+$r1)%10;

    return "{$code0}{$code1}{$code2}{$code3}";
}

try {
    $code = generateSerial($serial);
    echo $code; // ✅ plain code only
} catch (Exception $e) {
    echo "Invalid serial";
}
