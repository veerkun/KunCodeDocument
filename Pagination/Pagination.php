<?php


namespace core;


class Pagination
{

    var $baseUrl;
    var $totalRecords;
    var $recordsPerPage;
    var $currentPage;
    var $requestPageKey = 'page';
    var $request;
    var $end_size = 3;
    var $mid_size = 3;
    var $show_all = false;
    var $prev_next = true;
    var $prev_text = '&laquo; Previous';
    var $next_text = 'Next &raquo;';
    var $before_page_number = '';
    var $after_page_number = '';
    var $html_before = '';
    var $html_after = '';

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
    public function render($html_before = '', $html_after = '', $echo = true)
    {

        $page_links = $this->get_page_link();
        if(!is_array($page_links) ||empty($page_links))
            return '';
        $this->html_before = $html_before;
        $this->html_after = $html_after;
        $totalRecords = $this->totalRecords;
        $recordsPerPage = $this->recordsPerPage;
        $currentPage = $this->currentPage;

        // Get max pages and current page out of the current query, if available.
        $totalPage   = ceil($totalRecords/$recordsPerPage);
        $r = $this->html_before;
        $r .= "<ul class='page-numbers pagination pagination-sm m-none'>\n\t<li>";
        $r .= '<li class="disabled"><a href="#" aria-label="Total"><span aria-hidden="true">Page <strong>'. $currentPage . '</strong>/<strong>'. $totalPage . '</strong> | Records <strong>'. $totalRecords .'</strong></span></a></li>';
        $r .= "\n\t<li>";
        $r .= join( "</li>\n\t<li>", $page_links );
        $r .= "</li>\n</ul>\n";
        $r .= $this->html_after;
        if($echo)
            echo $r;
        return $r;

    }
    public function get_page_link()
    {
        if( empty($this->baseUrl) || empty($this->totalRecords) || empty($this->recordsPerPage) || empty($this->currentPage) || empty($this->requestPageKey) )
        {
            throw new \Exception('Missed property!');
        }
        $baseUrl = $this->baseUrl;
        $totalRecords = $this->totalRecords;
        $recordsPerPage = $this->recordsPerPage;
        $currentPage = $this->currentPage;
        $requestPageKey = $this->requestPageKey;

        // Get max pages and current page out of the current query, if available.
        $totalPage   = ceil($totalRecords/$recordsPerPage);
        $add_args = [];
        if(!empty($this->request) && is_array($this->request))
            $add_args = $this->request;

        if( isset($add_args[$requestPageKey]) )
            unset($add_args[$requestPageKey]);

        $url = $baseUrl . "?" .$requestPageKey . '=%#%';
        if(!empty($add_args)) {
            $queryString = '&'. http_build_query($add_args);
            $queryString = htmlentities($queryString);
            $url .= $queryString;
        }

       //$link

        // Who knows what else people pass in $args

        if ( $totalPage < 1 ) {
            return '';
        }
        $end_size = (int) $this->end_size; // Out of bounds?  Make it the default.
        if ( $end_size < 1 ) {
            $end_size = 1;
        }
        $mid_size = (int) $this->mid_size;
        if ( $mid_size < 0 ) {
            $mid_size = 2;
        }

        $page_links = array();
        $dots       = false;
        //$page_links[] = '<a href="#" aria-label="Total"><span aria-hidden="true">Page <strong>'. $currentPage . '</strong>/<strong>'. $totalPage . '</strong> | Records <strong>'. $totalRecords .'</strong></span></a>';
        if ( $this->prev_next && $currentPage && 1 < $currentPage ) :
            $link = str_replace( '%#%', $currentPage - 1, $url );
            $page_links[] = sprintf('<a class="prev page-numbers" href="%s">%s</a>', $link, $this->prev_text);
        endif;

        for ( $n = 1; $n <= $totalPage; $n++ ) :
            if ( $n == $currentPage ) :
                $page_links[] = sprintf(
                    '<span class="page-numbers current active">%s</span>',$this->before_page_number . $n . $this->after_page_number
                );

                $dots = true;
            else :
                if ( $this->show_all || ( $n <= $end_size || ( $currentPage && $n >= $currentPage - $mid_size && $n <= $currentPage + $mid_size ) || $n > $totalPage - $end_size ) ) :

                    $link = str_replace( '%#%', $n, $url );
                    $page_links[] = sprintf('<a class="page-numbers" href="%s">%s</a>',$link,$this->before_page_number .  $n  . $this->after_page_number);

                    $dots = true;
                elseif ( $dots && ! $this->show_all ) :
                    $page_links[] = '<span class="page-numbers dots">&hellip;</span>';

                    $dots = false;
                endif;
            endif;
        endfor;

        if ( $this->prev_next && $currentPage && $currentPage < $totalPage ) :
            $link = str_replace( '%#%', $currentPage + 1, $url );
            $page_links[] = sprintf('<a class="next page-numbers" href="%s">%s</a>', $link, $this->next_text);
        endif;

        return $page_links;

    }

    /*
     * Create pagination markup
     *
     * @param $baseUrl string
     * @param $page_key  ( page=number
     * @param $totalResults int
     * @param $resultsPerPage int
     * @param $currentPage int
     * @param $queryStringArray array
     */
    public static function pagination($baseUrl, $page_key, $totalResults, $resultsPerPage, $currentPage, $queryStringArray=[])
    {
        if(empty($page_key)) $page_key = 'page';
        //$currentPage = isset( $_GET[$page_key] ) ?  (int) ($_GET[$page_key])  : 1;

        //total pages to show
        $totalPages = ceil($totalResults/$resultsPerPage);

        //if only one page then no point in showing a single paginated link
        if($totalPages <=1 )
            return '';

        //build the query string if provided
        $queryString = '';
        if($queryStringArray) {
            if(isset($queryStringArray[$page_key]))
                unset($queryStringArray[$page_key]);
            $queryString = '&'.http_build_query($queryStringArray);
        }
        $queryString = htmlentities($queryString);

        //show not more than 3 paginated links on right and left side
        $page_show = 5;
        $rightLinks = $currentPage + $page_show;
        $previousLinks = $currentPage - $page_show;
        ob_start();

        ?>

            <ul class="pagination pagination-sm m-none">
                <li class="disabled">
                    <a href="#" aria-label="Total">
                        <span aria-hidden="true">Page <strong><?php echo $currentPage . '</strong>/<strong>'. $totalPages; ?></strong> | Records <strong><?php echo $totalResults; ?></strong></span>
                    </a>
                </li>
                <?php
                //if page number 1 is not shown then show the "First page" link
                if($previousLinks > 1) {
                    ?>
                    <li>
                        <a href="<?php echo $baseUrl.'?'.$page_key.'=1'.$queryString; ?>" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    <?php
                }

                //disable previous button when first page
                if($currentPage == 1) {
                    ?>
                    <li class="disabled">
                        <a href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php
                }

                //if current page > 1 only then show previous page
                if($currentPage > 1) {
                    ?>
                    <li>
                        <a href="<?php echo $baseUrl.'?'.$page_key.'='.($currentPage-1).$queryString; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php
                }

                //Create left-hand side links
                for($i = $previousLinks; $i <= $currentPage; $i++){
                    if($i>0) {
                        if($i==$currentPage) { ?>
                            <li class="active"><a href="#"><?php echo $i; ?></a></li>
                        <?php }
                        else { ?>
                            <li>
                                <a href="<?php echo $baseUrl.'?'.$page_key.'='.$i.$queryString; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php }
                    }
                }

                //middle pages
                if(false)
                    for($i=1; $i<=$totalPages; $i++) {
                        if($i==$currentPage) { ?>
                            <li class="active"><a href="#"><?php echo $i; ?></a></li>
                        <?php }
                        else { ?>
                            <li>
                                <a href="<?php echo $baseUrl.'?'.$page_key.'='.$i.$queryString; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php }
                    }

                //right side links
                for($i = $currentPage+1; $i < $rightLinks ; $i++){
                    if($i<=$totalPages){
                        if($i==$currentPage) { ?>
                            <li class="active"><a href="#"><?php echo $i; ?></a></li>
                        <?php }
                        else { ?>
                            <li>
                                <a href="<?php echo $baseUrl.'?'.$page_key.'='.$i.$queryString; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php
                        }
                    }
                }

                //if current page is not last page then only show next page link
                if($currentPage != $totalPages) { ?>
                    <li>
                        <a href="<?php echo $baseUrl.'?'.$page_key.'='.($currentPage+1).$queryString; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php
                }

                //if current page is last page then show next page link disabled
                if($currentPage == $totalPages) { ?>

                    <li class="disabled">
                        <a href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php
                }

                if($rightLinks<$totalPages) {
                    ?>
                    <li>
                        <a href="<?php echo $baseUrl.'?'.$page_key.'='.$totalPages.$queryString; ?>" aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                    <?php
                }
                ?>
            </ul>

        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}
