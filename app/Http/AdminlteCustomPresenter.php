<?php

namespace App\Http;

use X\LaravelMenus\Presenters\Presenter;

class AdminlteCustomPresenter extends Presenter
{
    protected $bgColor;
     public function __construct()
    {
        // Example: Fetch from session, or use default
        $sessionColor = session('business.theme_color', 'primary');

        // Match Tailwind color values (customize as needed)
        $bgColors = [
            'blue'    => '#00359e', // blue-900
            'orange'  => '#772917', // orange-900
            'red'     => '#7a271a', // red-900
            'green'   => '#074d31', // green-900
            'yellow'  => '#7a2e0e ', // yellow-900
            'purple'  => '#3e1c96', // purple-900
            'pink'    => '#831843', // pink-900
            'gray'    => '#111827', // gray-900
            'sky'     => '#0b4a6f', // sky-900
            'primary' => '#00359e', // fallback if session is 'primary'
        ];

        // Fallback to blue if key not found
        $this->bgColor = $bgColors[$sessionColor] ?? $bgColors['primary'];
    }
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper()
    {
        return '<div class="tw-flex-1 tw-px-2 tw-pt-4 tw-pb-4 tw-space-y-3 tw-overflow-y-auto tw-border-r tw-border-gray-200" id="side-bar" style="height:calc(100dvh - 60px); background-color:'.$this->bgColor.'">'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getCloseTagWrapper()
    {
        return '</div>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithoutDropdownWrapper($item)
    {
        return '<a href="'.$item->getUrl().'" title="" class="custom_sbb tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-tracking-tight tw-text-white tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap '.$this->getActiveState($item).'" '.$item->getAttributes().'>'.
        $this->formatIcon($item->icon).' <span class="tw-truncate">'.$item->title.'</span>'.
            '</a>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getActiveState($item, $state = ' custom_active_wdsbb')
    {
        return $item->isActive() ? $state : null;
    }

    /**
     * Get active state on child items.
     *
     * @param  string  $state
     * @return null|string
     */
    // public function getActiveStateOnChild($item, $state = 'tw-pb-1 tw-rounded-md tw-bg-gray-200 tw-text-primary-700')
    public function getActiveStateOnChild($item, $state = ' custom_active_psbb')
    {
        return $item->hasActiveOnChild() ? $state : null;
    }

    /**
     * {@inheritdoc}.
     */
    public function getDividerWrapper()
    {
        // Assuming a divider is just a visual space in this design
        return '<div class="tw-my-2"></div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getHeaderWrapper($item)
    {
        return '<div class="tw-px-3 tw-py-2 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider">'.$item->title.'</div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithDropDownWrapper($item)
    {
        // $dropdownToggle = '<a href="#" title="" class="drop_down tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-tracking-tight tw-text-gray-600 tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap hover:tw-text-gray-900 hover:tw-bg-gray-100 focus:tw-text-gray-900 focus:tw-bg-gray-100'.$this->getActiveStateOnChild($item).'" '.$item->getAttributes().'>'.
        // $this->formatIcon($item->icon).' <span class="tw-truncate">'.$item->title.'</span>'.
        // '<svg aria-hidden="true" class="svg tw-ml-auto tw-text-gray-500 tw-size-4 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">'.$this->getArray($item).
        //     '</svg>'.
        //     '</a>';
        $dropdownToggle = '<a href="#" title="" class="drop_down custom_sbb tw-flex tw-items-center tw-justify-between tw-gap-3 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-tracking-tight tw-text-white tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap '.$this->getActiveStateOnChild($item).'" '.$item->getAttributes().'>'.

            // Left side: icon + text
            '<div class="tw-flex tw-items-center tw-gap-2">'.
                $this->formatIcon($item->icon).
                '<span class="tw-truncate">'.$item->title.'</span>'.
            '</div>'.

            // Right side: dropdown arrow
            '<svg aria-hidden="true" class="svg tw-text-white tw-size-4 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">'.
                $this->getArray($item).
            '</svg>'.

        '</a>';


        $childItemsContainerStart = '';

        $childItemsContainerEnd = '';

        // Compile child menu items
        $childItems = $this->getChildMenuItems($item);

        // echo "here";
        // print_r($dropdownToggle);exit;

        return '<div class="'.$this->getActiveStateOnChild($item).'">'.$dropdownToggle.$childItemsContainerStart.$childItems.$childItemsContainerEnd.'</div>'.PHP_EOL;
    }

    /**
     * Get multi-level dropdown wrapper.
     *
     * Note: This example doesn't directly implement a multi-level dropdown, as it wasn't specified, but you could extend
     * the functionality similarly to `getMenuWithDropDownWrapper`, adjusting for deeper nesting.
     *
     * @param  \X\LaravelMenus\MenuItem  $item
     * @return string
     */
    public function getMultiLevelDropdownWrapper($item)
    {
        // Placeholder for multi-level dropdown functionality if needed
        return '';
    }

    /**
     * Get child menu items.
     *
     * @param  \X\LaravelMenus\MenuItem  $item
     * @return string
     */
    public function getChildMenuItems($item)
    {

        $children = '';
        $displayStyle = $item->hasActiveOnChild() ? 'block' : 'none';

        if (count($item->getChilds()) > 0) {

            // $children .= '<div class=" chiled tw-relative tw-mt-2 tw-mb-4 tw-pl-11" style="display:'.$displayStyle.'">
            // <div class="tw-absolute tw-inset-y-0 tw-w-px tw-h-full tw-bg-gray-200 tw-left-5"></div>
            // <div class="tw-space-y-3.5" style="padding:0px 1.5rem;">';
            $children .= '<div class=" chiled tw-relative tw-mt-2 tw-mb-4" style="display:'.$displayStyle.'">
            <div class="tw-space-y-1" style="padding:0px 2rem;">';

            foreach ($item->getChilds() as $child) {

                $isActive = $child->isActive() ? 'custom_active_child_sbb' : '';

                $children .= '<a href="'.$child->getUrl().'" title="" class="custom_child_sbb tw-p-1 tw-flex tw-text-xs tw-font-semibold tw-tracking-tight tw-text-gray-400 tw-truncate tw-whitespace-nowrap '.$isActive.'"'.$isActive.' "'.$child->getAttributes().'"'.$child->hasActiveOnChild().'>'.
                $child->getIcon().' <span>'.$child->title.'</span>'.
                    '</a>'.PHP_EOL;
            }

            $children .= '</div></div>';
        }

        return $children;
    }

    /**
     * Returns the icon HTML. If the icon is SVG, it returns directly; otherwise, it assumes it's a FontAwesome class and wraps it in an <i> tag.
     *
     * @param  string  $icon
     * @return string
     */
    protected function formatIcon($icon)
    {
        // Check if the icon string contains "<svg", indicating it's an SVG icon
        if (strpos($icon, '<svg') !== false) {
            return $icon; // Return the SVG icon directly
        } else {
            // Assume it's a FontAwesome icon and return it wrapped in an <i> tag
            return '<i class="'.$icon.'"></i>';
        }
    }

    public function getArray($item)
    {
        if ($item->hasActiveOnChild()) {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6 9l6 6l6 -6" />';
        } else {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M15 6l-6 6l6 6" />';
        }
    }
}
