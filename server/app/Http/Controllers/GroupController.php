<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class GroupController extends Controller
{

    public function index()
    {
        $query = Group::query()->withCount([
            'students as student_count',
            'students as complete_count' => function ($query) {
                $query->where('complete', 1);
            }
        ]);
        $query = $this->queryFilter($query);
        if ($this->req->get('getOptions') == 1) {
            return $this->getOptions($query);
        } else {
            return $this->paginate($query);
        }
    }

    public function create()
    {
        try {
            $groups = $this->req->all();
            $res = true;
            foreach ($groups as $group){
                // Todo: Validate
                $res = Group::query()->firstOrCreate([
                    'name' => $group['name']
                ]);
            }
            return $this->json($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function show($id)
    {
        $item = Group::query()->findOrFail($id);
        return $this->json($item);
    }

    public function update($id)
    {
        $item = Group::query()->findOrFail($id);
        $validator = $this->ruleValidator($item->rules(),$item->ruleMessage());
        if ($validator){
            return $validator;
        }
        try {
            $input = $this->req->all();
            // Todo: Validate
            $item->update($input);
            return $this->json($item);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function delete($id)
    {
        $item = Group::query()->findOrFail($id);
        try {
            $item->delete();
            return $this->json();
        } catch (\Exception $e) {
            return $this->error('Delete failed');
        }
    }

    public function updateBatch()
    {
        if ($this->req->input('all') == 1){
            $data = $this->req->except('all');
            try {
                Group::query()->update($data);
                return $this->json();
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
        }

        $ids = (array)$this->req->get('ids');
        $data = $this->req->except('ids');
        try {
            Group::query()->whereIn('id', $ids)->update($data);
            return $this->json();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function deleteBatch()
    {
        $ids = (array)$this->req->get('ids');
        try {
            Group::query()->whereIn('id', $ids)->delete();
            return $this->json();
        } catch (\Exception $e) {
            return $this->error('Delete failed');
        }
    }

}
