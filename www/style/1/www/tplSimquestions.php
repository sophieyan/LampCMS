<?php
/**
 *
 * PHP 5.3 or better is required
 *
 * @package    Global functions
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2012 (or current year) Dmitri Snytkine
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt The GNU General Public License (GPL) version 3
 * @link       http://cms.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */

/**
 * This template is used when generating
 * div with similar questions while
 * parsing one question (submitted question)
 *
 * The result is then stored in QUESTIONS collection
 * under the sims key
 *
 * Usually this template is not used during
 * page rendering, only during saving
 * data
 *
 * @author Dmitri Snytkine
 *
 */
class tplSimquestions extends Lampcms\Template\Fast
{
    /**
     * For this template we don't
     * want to add html debug code
     * even when in debug mode
     * because result of this parsed
     * question is stored with the
     * question.
     *
     * @var bool
     */
    protected static $debug = false;

    protected static function func(&$a)
    {
        $a['intro'] = trim($a['intro']);
    }

    protected static $vars = array(
        'qid' => '',
        'url' => '',
        'intro' => '',
        'title' => '',
        'hts' => '');

    protected static $tpl = '<div class="simq"><a href="{_WEB_ROOT_}/{_viewquestion_}/{_QID_PREFIX_}%1$s/%2$s" title="%3$s">%4$s</a><br><span class="ts" title="%5$s">%5$s</span><br></div>';
}
