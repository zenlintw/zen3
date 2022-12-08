SET NAMES utf8;

use `WM_10001`;

/* portal 預設值，新增常見問題及行事曆 */
INSERT INTO `WM_portal` (`portal_id`, `key`, `value`) VALUES
  ('content_sw',  'news',         'true'),
  ('content_sw',  'calendar',     'false'),
  ('news',        'title',        '最新消息'), 
  ('calendar',    'title',        '最新活動');