<?php

namespace App\View\Composers;

use App\Traits\CarSearch;
use Illuminate\View\View;

class LangdingComposer
{
    use CarSearch;

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        // Chỉ inject vào các view langding
        if (str_starts_with($view->getName(), 'langding.')) {
            // ✅ Mặc định load data cho 'oto' (ô tô) vì button "chon-xe-oto" là active mặc định
            $view->with([
                'carSearchData' => $this->getCarSearchDataByVehicleType('oto'),
                'tireSizeData' => $this->getTireSizeDataByVehicleType('oto'),
            ]);
        }
    }
}
