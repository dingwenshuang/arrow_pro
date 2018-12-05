<?php
/**
 *
 *
 * 分类
 */

namespace app\admin\controller;




use app\api\model\Category as CategoryModel;
use app\api\model\RiceModel;
use think\facade\Request;
use app\api\model\Product as ProductModel;
use app\api\model\Activity as ActivityModel;
use app\admin\model\Common as CommonModel;
use app\admin\model\Pklog as PklogModel;
use app\admin\model\Pklog;

class Activity extends BaseController
{
    // protected $beforeActionList = [
    //     'checkAdminScope' => ['only' => 'index,addac,editac,delac,delall'],
    // ];

    public function index(){

       $acts= ActivityModel::where('delete_time','=',null)
       ->select();
        //dump($re->count());
       return view('',['acts'=>$acts]);
    }

    public function addac(){

        if(Request::isPost()){
           // dump(Request::file());exit;
            Request::file('image');
            $image=$this->upload('image');
            $info=[
                'rid'=>input('post.rid'),
                'title'=>input('post.title'),
                'desc'=>input('post.desc'),
                'detail'=>input('post.detail'),
                'start_time'=>strtotime(input('post.start_time')),
                'end_time'=>strtotime(input('post.end_time')),
                'create_time'=>time(),
            ];
            if(is_string($image)){
                $info['image']=$image;
            }
            ActivityModel::create($info);
            return redirect('admin/activity/index');
        }else{

            $cates= RiceModel::where('delete_time','=',null)
                ->select();

            return view('',['cates'=>$cates,'cates'=>$cates]);
        }

    }

    public function editac($id){

        if(Request::isPost()){
             //dump(input('post.'));exit;
            $image=$this->upload('image');
            $info=[
                'rid'=>input('post.rid'),
                'title'=>input('post.title'),
                'desc'=>input('post.desc'),
                'detail'=>input('post.detail'),
                'start_time'=>strtotime(input('post.start_time')),
                'end_time'=>strtotime(input('post.end_time')),
                'update_time'=>time(),
            ];
            if(is_string($image)){
                $info['image']=$image;
            }
            ActivityModel::where('id','=',$id)
            ->update($info);
            return redirect('admin/activity/index');
        }else{
            $act=ActivityModel::find($id);
            $cates= RiceModel::where('delete_time','=',null)
                ->select();
            return view('',['act'=>$act,'cates'=>$cates]);
        }

    }

    public function delac(){
        $id=Request::param('id');
        $id=explode(',',$id);

        ActivityModel::where('id','in', $id)
            ->update(['delete_time'=>time(),'update_time'=>time()]);
        return json(['code'=>200]);
    }

    public function delall(){
        ActivityModel::where('id','>',0)->delete();
        return json(['code'=>200]);
    }

    /**
     * @access public
     * @return mixed
     * @context 用户列表
     */
    public function pk()
    {
        $table = 'pk';//数据库名称
        $where['delete_time'] = null;//查询条件
        $field = 'id,username,phone';//查询字段

        $list = CommonModel::SelectData($table, $where, $field);
        $count = count($list);
        $this->assign([
            'list' => $list,
            'count' => $count
        ]);
        return view();
    }

    /**
     * @access public
     * @return mixed
     * @context pk录入
     */
    public function pkdo(){

        $post = Request()->post();
//        return json(['code' => 500, 'errmsg' => $post]);
        if($post){
            if($post['type'] == 1){//增加积分
                $info = PklogModel::where('delete_time',null)->where('user_id',$post['id']) -> find();
//                    return json(['code' => 500, 'errmsg' => $info]);
                if($info){
                    $info->fraction = $post['number'] + $info['fraction'];
                    $info->update_time = time();
                    $res = $info->save();
                    if($res>0){
                        return json(['code' => 200]);
                    }else{
                        return json(['code' => 500, 'errmsg' => '系统错误，录入失败！']);
                    }
                }else{
                    $user              = new Pklog();
                    $user->user_id     = $post['id'];
                    $user->fraction    = $post['number'];
                    $user->create_time    = time();
                    $result = $user->save();
                    if($result>0){
                        return json(['code' => 200]);
                    }else{
                        return json(['code' => 500, 'errmsg' => '系统错误，录入失败！']);
                    }
                }
            }else{//减少积分
                $info = PklogModel::where('delete_time',null)->where('user_id',$post['id']) -> find();
                if($info){
                    $fraction = $info['fraction'] - $post['number'];
                    if($fraction<0){
                        return json(['code' => 500, 'errmsg' => '减少pk点超过用户上限！']);
                    }else{
                        $info->fraction = $fraction;
                        $info->update_time = time();
                        $result = $info -> save();
                        if($result>0){
                            return json(['code' => 200]);
                        }else{
                            return json(['code' => 500, 'errmsg' => '系统错误，录入失败！']);
                        }
                    }
                }else{
                    return json(['code' => 500, 'errmsg' => '该用户无pk记录，无法减少积分！']);
                }
            }
        }else{
            return json(['code' => 500, 'errmsg' => '参数错误']);
        }
    }

    /**
     * @access public
     * @return mixed
     * @context pk录入
     */
    public function kong(){
        $pk = new Pklog();
        $log = $pk::where('delete_time',null)->select();
        if($log){
            $result = PklogModel::where('id','>' ,0)
                ->update(['delete_time' => time()]);


//            $data['delete_time'] = null;
//            $result = PklogModel::where('id','>',0)->saveAll($data);

            if($result>0){
                return json(['code' => 200]);
            }else{
                return json(['code' => 500, 'errmsg' => '系统错误，清空失败！']);
            }
        }else{
            return json(['code' => 200]);
        }
    }

}