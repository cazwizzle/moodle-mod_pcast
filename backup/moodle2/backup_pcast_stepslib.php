<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 Stephen Bourget and Jillaine Beeckman
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete choice structure for backup, with file and id annotations
 */
class backup_pcast_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated

        $pcast = new backup_nested_element('pcast', array('id'), array(
            'userid','name', 'intro', 'introformat', 'userscancomment',
            'userscancategorize', 'userscanpost', 'requireapproval', 'displayauthor',
            'displayviews', 'image', 'imageheight', 'imagewidth','rssepisodes',
            'rsssortorder', 'enablerssfeed', 'enableitunes', 'visible', 'explicit',
            'subtitle', 'keywords', 'topcategory','nestedcategory','assessed',
            'assesstimestart','assesstimefinish', 'scale', 'timecreated', 'timemodified'));

        $episodes = new backup_nested_element('episodes');

        $episode = new backup_nested_element('episode', array('id'), array(
            'userid', 'name', 'summary', 'mediafile', 'duration', 'explicit',
            'subtitle', 'keywords', 'topcategory', 'nestedcategory', 'timecreated','timemodified',
            'approved', 'sequencenumber'));

        $views = new backup_nested_element('views');

        $view = new backup_nested_element('view', array('id'), array(
            'episodeid', 'userid', 'views', 'lastview'));


        // Build the tree

        $pcast->add_child($episodes);
        $episodes->add_child($episode);

        $episode->add_child($views);
        $views->add_child($view);

        // Define sources

        $pcast->set_source_table('pcast', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {

            $episode->set_source_sql('
            SELECT *
              FROM {pcast_episodes}
             WHERE pcastid = ?',
            array(backup::VAR_PARENTID));

            $view->set_source_sql('
            SELECT *
              FROM {pcast_views}
             WHERE episodeid = ?',
            array(backup::VAR_PARENTID));        }

        // Define id annotations

        $pcast->annotate_ids('user', 'userid');
        $episode->annotate_ids('user', 'userid');
        $view->annotate_ids('user', 'userid');

        // Define file annotations

        $pcast->annotate_files('mod_pcast', 'intro', null); // This file area hasn't itemid
        $pcast->annotate_files('mod_pcast','episode','id');
        $pcast->annotate_files('mod_pcast','logo','image');

        // Return the root element (pcast), wrapped into standard activity structure
        return $this->prepare_activity_structure($pcast);

    }
}