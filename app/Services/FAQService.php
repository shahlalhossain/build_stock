<?php

namespace App\Services;

//use App\Events\FAQ\FAQCreated;
//use App\Events\FAQ\FAQUpdated;
//use App\Events\FAQ\FAQDestroyed;
//use App\Events\FAQ\FAQRestored;
//use App\Events\FAQ\FAQDeleted;
use App\Models\FAQ;
use App\Exceptions\GeneralException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

/**
 * Class FAQService.
 */
class FAQService extends BaseService
{
    /**
     * FAQService Constructor.
     *
     * @param FAQ $faq
     */
    public function __construct(FAQ $faq)
    {
        $this->model = $faq;
    }

    /**
     * @param array $data
     * @return FAQ
     *
     * @throws GeneralException
     * @throws \Throwable
     */
    public function createFAQ(array $data = []): FAQ
    {
        DB::beginTransaction();

        $pctData = [
            'faq_category_id'   => $data['faq_category_id'] ?? null,
            'question'          => $data['question'] ?? null,
            'answer'            => $data['answer'] ?? null,
            'language'          => $data['language'] ?? null,
            'is_active'         => 1,
            'created_by'        => Auth::user()->id,
            'updated_by'        => Auth::user()->id,
        ];

        try {
            $faq = $this->storeFAQ($pctData);
            //event(new FAQCreated($faq));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw new GeneralException(__('There was a Problem Creating FAQ. Please Try Again.'));
        }

        DB::commit();

        return $faq;
    }

    /**
     * @param FAQ $faq
     * @param array $data
     * @return FAQ
     *
     * @throws \Throwable
     */
    public function update(FAQ $faq, array $data = []) : FAQ
    {
        DB::beginTransaction();

        $faqData = [
            'faq_category_id'   => $data['faq_category_id'] ?? null,
            'question'          => $data['question'] ?? null,
            'answer'            => $data['answer'] ?? null,
            'language'          => $data['language'] ?? null,
            'updated_by'        => Auth::id(),
            'updated_at'        => now(),
        ];

        try {
            $this->updateFAQ($faq, $faqData);
            //event(new FAQUpdated($faq));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw new GeneralException(__('There was a Problem Updating the FAQ. Please Try Again.'));
        }

        DB::commit();

        return $faq->refresh();
    }

    /**
     * @param array $data
     * @return FAQ
     */
    protected function storeFAQ(array $data = []) : FAQ
    {
        // TODO: Manage the FAQCreated Event Here or in FAQObserver
        return $this->model::create($data);
    }

    protected function updateFAQ(FAQ $faq, array $data = []) : bool
    {
        // TODO: Manage the FAQUpdated Event Here or in FAQObserver
        return $faq->update($data);
    }

    public function destroyFAQ($id) : bool
    {
        DB::beginTransaction();

        try {
            $faq = FAQ::findOrFail((int)$id);

            if (!$faq) { return false; }

            $faq->is_active   = false;
            $faq->deleted_by  = Auth::id();
            $faq->deleted_at  = now();

            $result = $faq->save();
            // event(new FAQDestroyed($faq));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('FAQ Destroy Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy FAQ.'));
        }
    }

    public function restoreFAQ($id) : bool
    {
        DB::beginTransaction();
        try {
            $faq = FAQ::withTrashed()->findOrFail($id);
            $faq->restore();
            $faq->is_active = true;
            $faq->deleted_by = null;

            $result = $faq->save();
            // event(new FAQRestored($faq));
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('FAQ Restore Failed: ' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the FAQ.'));
        }
    }

    public function deleteFAQ($id) : bool
    {
        DB::beginTransaction();
        try {
            $faq = FAQ::withTrashed()->findOrFail($id);
            $result = $faq->forceDelete();
            DB::commit();
            // event(new FAQDeleted($faq));
            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('FAQ Deletion Failed in Service:' . $exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the FAQ.'));
        }
    }
}
