<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 視圖
 *
 * 此為介面類別(interface)，必須實作(implements)使用
 */
interface View {

    public function getHeaders();

    public function getBody();
}
