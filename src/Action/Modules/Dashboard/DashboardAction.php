<?php

namespace App\Action\Modules\Dashboard;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Settings\SettingsDashboardDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent  = $this->renderTemplate( true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     * @throws Exception
     */
    protected function renderTemplate($ajaxRender = false): Response
    {
        $dashboardSettings               = $this->app->settings->settingsLoader->getSettingsForDashboard();
        $dashboardWidgetsVisibilityDtos  = null;

        if( !empty($dashboardSettings) ){
            $dashboardSettingsJson            = $dashboardSettings->getValue();
            $dashboardSettingsDto             = SettingsDashboardDTO::fromJson($dashboardSettingsJson);
            $dashboardWidgetsVisibilityDtos   = $dashboardSettingsDto->getWidgetSettings()->getWidgetsVisibility();
        }

        $schedules     = $this->controllers->getDashboardController()->getIncomingSchedulesInformation();
        $allTodo       = $this->controllers->getDashboardController()->getGoalsTodoForWidget();
        $goalsPayments = $this->controllers->getDashboardController()->getGoalsPayments();

        $pendingIssues   = $this->controllers->getDashboardController()->getPendingIssues();
        $issuesCardsDtos = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues($pendingIssues);

        $data = [
            'dashboard_widgets_visibility_dtos'  => $dashboardWidgetsVisibilityDtos,
            'schedules'                          => $schedules,
            'all_todo'                           => $allTodo,
            'goals_payments'                     => $goalsPayments,
            'issues_cards_dtos'                  => $issuesCardsDtos,
            'ajax_render'                        => $ajaxRender,
        ];

        return $this->render("modules/my-dashboard/dashboard.html.twig", $data);
    }

}