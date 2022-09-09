<?php


class Macro_views
{

    public static function booking_cus_view($view)
    {
        ?>
        <table>
            <tr>
                <td style="width: 100%">预约状态</td>
                <td>
                    <select  name="booking_status">
                       <option value="0" <?php echo $view->booking_status == '0' ? 'selected':''?>>未签到</option>
                       <option value="1" <?php echo $view->booking_status == '1' ? 'selected':''?>>已签到</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 100%">预约时间</td>
                <td>
                   <input name="booking_time"  value="<?php echo $view->booking_time ?>" type="datetime-local"/>

                </td>
            </tr>

        </table>
        <?php

    }

}

