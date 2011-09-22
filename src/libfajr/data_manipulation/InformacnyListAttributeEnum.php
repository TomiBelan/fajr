<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use InvalidArgumentException;

/**
 * Class defining possible items on information sheet.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class InformacnyListAttributeEnum
{
  const SKOLA_FAKULTA = 'nazov_vysokej_skoly_a_fakulty';
  const KOD = 'kod';
  const NAZOV = 'nazov';
  const STUDIJNY_PROGRAM = 'studijny_program';
  const GARANTUJE = 'garantuje';
  const ZABEZPECUJE = 'zabezpecuje';
  const OBDOBIE_STUDIA_PREDMETU = 'obdobie_studia_predmetu';
  const FORMA_VYUCBY = 'forma_vyucby';
  const VYUCBA_TYZDENNE = 'odporucany_rozsah_vyucby_tyzdenne_v_hodinách';
  const VYUCBA_SPOLU = 'odporucany_rozsah_vyucby_spolu_v_hodinách';
  const POCET_KREDITOV = 'pocet_kreditov';
  const PODMIENUJUCE_PREDMETY = 'podmienujuce_predmety';
  const OBSAHOVA_PREREKVIZITA = 'obsahova_prerekvizita';
  const SPOSOB_HODNOTENIA_A_SKONCENIA = 'sposob_hodnotenia_a_skoncenia_studia_predmetu';
  const PRIEBEZNE_HODNOTENIE = 'priebezne_hodnotenie';
  const ZAVERECNE_HODNOTENIE = 'zaverecne_hodnotenie';
  const CIEL_PREDMETU = 'ciel_predmetu';
  const OSNOVA_PREDMETU = 'strucna_osnova_predmetu';
  const LITERATURA = 'literatura';
  const VYUCOVACI_JAZYK = 'jazyk_v_ktorom_sa_predmet_vyucuje';
  const DATUM_POSLEDNEJ_UPRAVY = 'datum_poslednej_upravy_listu';

  /**
   * Returns slovak description for chosen attribute.
   *
   * @param string $name Attribute name.
   * @returns string Attribute description in utf8.
   * @throws InvalidArgumentException If chosen attribute does not exists.
   */
  public static function getUnicodeName($name)
  {
    $attributeDescriptions = array(
      self::SKOLA_FAKULTA => 'Názov vysokej školy, názov fakulty',
      self::KOD => 'Kód',
      self::NAZOV => 'Názov',
      self::STUDIJNY_PROGRAM => 'Študijný program',
      self::GARANTUJE => 'Garantuje',
      self::ZABEZPECUJE => 'Zabezpečuje',
      self::OBDOBIE_STUDIA_PREDMETU => 'Obdobie štúdia predmetu',
      self::FORMA_VYUCBY => 'Forma výučby',
      self::VYUCBA_TYZDENNE => 'Odporúčaný rozsah výučby týždenne (v hodinách)',
      self::VYUCBA_SPOLU => 'Odporúčaný rozsah výučby spolu (v hodinách)',
      self::POCET_KREDITOV => 'Počet kreditov',
      self::PODMIENUJUCE_PREDMETY => 'Podmieňujúce predmety',
      self::OBSAHOVA_PREREKVIZITA => 'Obsahová prerekvizita',
      self::SPOSOB_HODNOTENIA_A_SKONCENIA => 'Spôsob hodnotenia a skončenia štúdia predmetu',
      self::PRIEBEZNE_HODNOTENIE => 'Priebežné hodnotenie',
      self::ZAVERECNE_HODNOTENIE => 'Záverečné hodnotenie',
      self::CIEL_PREDMETU => 'Cieľ predmetu',
      self::OSNOVA_PREDMETU => 'Stručná osnova predmetu',
      self::LITERATURA  => 'Literatúra',
      self::VYUCOVACI_JAZYK => 'Jazyk, v ktorom sa predmet vyučuje',
      self::DATUM_POSLEDNEJ_UPRAVY => 'Dátum poslednej úpravy listu',
    );

    if (array_key_exists($name, $attributeDescriptions)) return $attributeDescriptions[$name];
    else throw new InvalidArgumentException('Attribute "'.$name.'" does not exists.');
  }
  
}
