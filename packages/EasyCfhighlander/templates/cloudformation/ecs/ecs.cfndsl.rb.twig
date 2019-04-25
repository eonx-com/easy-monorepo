CloudFormation do 

    ECS_Cluster('EcsCluster') {
        ClusterName FnSub("${EnvironmentName}-#{cluster_name}") if defined? cluster_name
    }

    Output("EcsCluster") {
        Value(Ref('EcsCluster'))
        Export FnSub("${EnvironmentName}-#{component_name}-EcsCluster")
      }
    Output("EcsClusterArn") {
        Value(FnGetAtt('EcsCluster','Arn'))
        Export FnSub("${EnvironmentName}-#{component_name}-EcsClusterArn")
    }

end