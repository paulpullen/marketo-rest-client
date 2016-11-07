<?php
/*
 * This file is part of the Marketo REST API Client package.
 *
 * (c) 2014 Daniel Chesterton
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSD\Marketo\Response;

use CSD\Marketo\Response as Response;

/**
 * Response for the addCustomActivities API method.
 *
 */
class AddCustomActivitiesResponse extends Response
{
    /**
     * Get the status of the custom activity. If no custom activity ID is given, it returns the status of the first one.
     *
     * @param $id
     * @return bool
     */
    public function getStatus($id)
    {
        if ($this->isSuccess()) {
            if (!$id) {
                $result = $this->getResult();
                return $result[0]['status'];
            }

            foreach ($this->getResult() as $row) {
                if ($row['id'] == $id) {
                    return $row['status'];
                }
            }
        }

        return false;
    }
}
