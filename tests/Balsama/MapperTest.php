<?php

namespace Balsama;

use Balsama\BostonRecords\Mapper;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    public function testGetOrdered(): void
    {
        $result = Mapper::getOrdered(['apple', 'banana', 'pear'], [1, 0, 2]);
        $this->assertEquals('banana', $result[0]);
        $this->assertEquals('apple', $result[1]);
        $this->assertEquals('pear', $result[2]);
    }

    public function testGetOrderedCitation2021One(): void
    {
        $valueString = '"Boston Police Special OPS",PD_649,9047,"2011-01-01 00:00:00.000",4,,PM,OPERATOR,M9817383,CIVIL,8991,"STOP/YIELD, FAIL TO * c89 §9",R,Responsible,Roxbury,N,N,CT_002,BLACK,MALE,1961,MA,D,No,PAN,MA,0,,No,,Unk,No,50.00,Y,N,0,0,0,Responsible,,,,';
        $valuesArray = str_getcsv($valueString);
        $result = Mapper::getOrdered($valuesArray, Mapper::CITATION_2021);

        $this->assertCount(40, $result);
        $this->assertEquals("50.00", $result['amount']);
    }

    public function testGetOrderedCitation2021Two(): void
    {
        $valueString = '"Boston Police Area K",PD_653,106708,"2012-11-12 00:00:00.000",00,00,AM,OPERATOR,R2468186,CIVIL,9014B0V2,"SIGNAL, FAIL TO * c90 §14B",NR,"Not Responsible",Brighton,N,N,CT_008,WHITE,MALE,1967,MA,D,No,PAR,MA,2007,PONTIAC,No,BLACK,Unk,No,0.00,N,N,0,0,0,"Not Responsible",,,,VIOL';
        $valuesArray = str_getcsv($valueString);
        $result = Mapper::getOrdered($valuesArray, Mapper::CITATION_2021);

        $this->assertCount(40, $result);
        $this->assertEquals('PONTIAC', $result['make_model']);
    }

    public function testGetOrderedCition2024One(): void
    {
        $valueString = 'Boston Police District E-18,PD_BPL,126598,1/1/2021,2,05,PM,1/1/2021,OPERATOR,,CRIM,9017A4,SPEEDING RATE OF SPEED EXCEEDING POSTED LIMIT  c90 §17,NR,Not Responsible,2/19/2021,Dorchester,N,N,CT_007,BLACK,MALE,1998,MA,D,No,,,0,,NO,,NO,NO,0.0000,N,Y,0,0,UNK,UNK,UNK';
        $valuesArray = str_getcsv($valueString);
        $result = Mapper::getOrdered($valuesArray, Mapper::CITATION_2024);

        $this->assertCount(40, $result);
        $this->assertEquals('0.0000', $result['amount']);
    }
}
