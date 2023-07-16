<?php

namespace MediaWiki\Extension\PkgStore;

use MWException;
use OutputPage, Parser, PPFrame, Skin;

/**
 * Class MW_EXT_InfoBox
 */
class MW_EXT_InfoBox
{
  /**
   * Get type.
   *
   * @param $type
   *
   * @return array
   */
  private static function getInfoBox($type): array
  {
    $type = MW_EXT_Kernel::getYAML(__DIR__ . '/store/' . $type . '.yml');
    return $type ?? [] ?: [];
  }

  /**
   * Get icon.
   *
   * @param $type
   *
   * @return string
   */
  private static function getTypeIcon($type): string
  {
    $type = self::getInfoBox($type) ? self::getInfoBox($type) : '';
    return $type['icon'] ?? '' ?: '';
  }

  /**
   * Get type property.
   *
   * @param $type
   *
   * @return string
   */
  private static function getTypeProperty($type): string
  {
    $type = self::getInfoBox($type) ? self::getInfoBox($type) : '';
    return $type['property'] ?? '' ?: '';
  }

  /**
   * Get field.
   *
   * @param $type
   * @param $field
   *
   * @return array
   */
  private static function getField($type, $field): array
  {
    $type = self::getInfoBox($type);
    return $type['field'][$field] ?? [] ?: [];
  }

  /**
   * Get field property.
   *
   * @param $type
   * @param $field
   *
   * @return string
   */
  private static function getFieldProperty($type, $field): string
  {
    $field = self::getField($type, $field) ? self::getField($type, $field) : '';
    return $field['property'] ?? '' ?: '';
  }

  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return void
   * @throws MWException
   */
  public static function onParserFirstCallInit(Parser $parser): void
  {
    $parser->setFunctionHook('infobox', [__CLASS__, 'onRenderTag'], Parser::SFH_OBJECT_ARGS);
  }

  /**
   * Render tag function.
   *
   * @param Parser $parser
   * @param PPFrame $frame
   * @param array $args
   *
   * @return string|null
   */
  public static function onRenderTag(Parser $parser, PPFrame $frame, array $args): ?string
  {
    // Get options parser.
    $getOption = MW_EXT_Kernel::extractOptions($frame, $args);

    // Argument: type.
    $getBoxType = MW_EXT_Kernel::outClear($getOption['type'] ?? '' ?: '');
    $outBoxType = empty($getBoxType) ? '' : MW_EXT_Kernel::outNormalize($getBoxType);

    // Argument: title.
    $getItemTitle = MW_EXT_Kernel::outClear($getOption['title'] ?? '' ?: '');
    $outItemTitle = empty($getItemTitle) ? MW_EXT_Kernel::getMessageText('infobox', 'block-title') : $getItemTitle;

    // Argument: image.
    $getItemImage = MW_EXT_Kernel::outClear($getOption['image'] ?? '' ?: '');

    // Argument: caption.
    $getItemCaption = MW_EXT_Kernel::outClear($getOption['caption'] ?? '' ?: '');
    $outItemCaption = empty($getItemCaption) ? '' : '<div>' . $getItemCaption . '</div>';

    // Out item type.
    $outItemType = empty($getBoxType) ? '' : MW_EXT_Kernel::outNormalize($getBoxType);

    // Check infobox type, set error category.
    if (!self::getInfoBox($outBoxType)) {
      $parser->addTrackingCategory('mw-infobox-error-category');

      return null;
    }

    // Check infobox property.
    if (self::getTypeProperty($outBoxType)) {
      $typeProperty = self::getTypeProperty($outBoxType);
    } else {
      $typeProperty = '';
    }

    // Out image or icon.
    $outItemImage = empty($getItemImage) ? '<i class="' . self::getTypeIcon($outBoxType) . '"></i>' : $getItemImage;

    // Out HTML.
    $outHTML = '<div class="mw-infobox mw-infobox-' . $outBoxType . ' navigation-not-searchable" itemscope itemtype="http://schema.org/' . $typeProperty . '">';
    $outHTML .= '<div class="infobox-item infobox-item-title"><div>' . $outItemTitle . '</div><div>' . MW_EXT_Kernel::getMessageText('infobox', $outItemType) . '</div></div>';
    $outHTML .= '<div class="infobox-item infobox-item-image"><div>' . $outItemImage . '</div>' . $outItemCaption . '</div>';

    foreach ($getOption as $key => $value) {
      $key = MW_EXT_Kernel::outNormalize($key);
      $field = self::getField($outBoxType, $key);
      $title = $outBoxType . '-' . MW_EXT_Kernel::outNormalize($key);

      if (self::getFieldProperty($outBoxType, $key)) {
        $fieldProperty = self::getFieldProperty($outBoxType, $key);
      } else {
        $fieldProperty = '';
      }

      if ($field && !empty($value)) {
        $outHTML .= '<div class="infobox-grid infobox-item infobox-item-' . $title . '">';
        $outHTML .= '<div class="item-title">' . MW_EXT_Kernel::getMessageText('infobox', $title) . '</div>';
        $outHTML .= '<div class="item-value" itemprop="' . $fieldProperty . '">' . MW_EXT_Kernel::outClear($value) . '</div>';
        $outHTML .= '</div>';
      }
    }

    $outHTML .= '</div>';

    // Out parser.
    return $outHTML;
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return void
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin): void
  {
    $out->addModuleStyles(['ext.mw.infobox.styles']);
  }
}
